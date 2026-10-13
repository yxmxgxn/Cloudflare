"""
アイドル在籍タイムライン生成スクリプト
Google Sheets（公開設定）からデータを取得してPNG出力

【フォント準備】Colabで最初に1回だけ実行:
    !apt-get -qq install fonts-noto-cjk
    import matplotlib.font_manager as fm
    fm.fontManager.addfont("/usr/share/fonts/opentype/noto/NotoSansCJK-Regular.ttc")

【スプシの列名】
    名前, 生年月日, ハロプロ卒業日, デビュー日, グループ加入日,
    カラー名, 旧or別カラー, 色変更日, 旧or別カラー名2,カラー, 旧or別カラー2, 色変更日, 色変更日2,
    リーダー就任, リーダー交代, サブリーダー就任, サブリーダー交代
"""

import pandas as pd
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as mpatches
import matplotlib.font_manager as font_manager
from io import StringIO
from datetime import datetime, date, timezone, timedelta
from pathlib import Path

from sheets import fetch_text

# ════════════════════════════════════════════════════════
#  ★ 設定エリア
# ════════════════════════════════════════════════════════
# メンバーマスター（generate_members.py と同じ公開シート）
PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vQFLNyFnLg0oxCm_39mnbRCwttmKUKffQk7_nAZxeydw41KBaFHTR9TqHsHvYvcsZ5Bgl1fQlBPCs0Q/pub"
)
SHEET_GID = "837040930"

# 取得したCSVがこの列を持っていなければ即エラーにする
REQUIRED_COLS = ["グループNo", "名前", "生年月日", "グループ加入日", "カラー"]

REPO_ROOT = Path(__file__).resolve().parent.parent
TIMELINE_DIR = REPO_ROOT / "Timeline"
# ページ下部に出す更新日（他のジェネレータと同じ形式）
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "timeline_generated.php"

# 生成するグループのリスト
GROUPS = [
    {"name": "モーニング娘。",    "no": "2",  "output": "timeline_mm.png"},
    {"name": "アンジュルム",      "no": "11",  "output": "timeline_anj.png"},
    {"name": "Juice=Juice",      "no": "12",  "output": "timeline_juice.png"},
    {"name": "つばきファクトリー", "no": "15", "output": "timeline_tsubaki.png"},
    # サブグループがあるので、サブID→加入日→ソートIDの順で並べ、境目に点線とユニット名を入れる
    {"name": "BEYOOOOONDS",      "no": "16", "output": "timeline_beyo.png",
     "sub_names": {1: "雨ノ森 川海", 2: "CHICA#TETSU", 3: "SeasoningS"},
     # 最初の加入日（2017/5/5）ではなく、グループとしての結成日を出す
     "formation": date(2018, 10, 19)},
]

FONT_PATH  = "/usr/share/fonts/opentype/noto/NotoSansCJK-Regular.ttc"
FONT_NAME  = "Noto Sans CJK JP"

# ── レイアウト定数 ───────────────────────────────────────
BAR_HEIGHT    = 0.5    # バーの太さ
ROW_HEIGHT    = 0.52   # 1行あたりの高さ（名前+加入年齢の2行分を考慮して少し広め）
FIG_WIDTH     = 16     # 図の横幅（インチ）
FIG_MARGIN_T  = 1.5    # 上余白（タイトル分）
FIG_MARGIN_B  = 1.6    # 下余白（X軸分）

# フォントサイズ（全体+2pt）
FONT_SIZE_NAME    = 15   # メンバー名・在籍日数（大）
FONT_SIZE_SUB     = 12   # 年月・加入年齢（小）
FONT_SIZE_YEAR    = 15   # X軸の年ラベル
FONT_SIZE_TITLE   = 18   # タイトル

# 役職ライン
LEADER_COLOR    = "gold"
SUBLEADER_COLOR = "silver"
ROLE_LINE_W     = 3.5
ROLE_EDGE_W     = 1.2

# 色
COLOR_BG      = "white"
COLOR_TEXT    = "black"
COLOR_GRID    = "#cccccc"
COLOR_SPINE   = "#aaaaaa"
COLOR_MISSING = "#aaaaaa"
COLOR_SUBNAME = "#555555"   # サブグループ名
# ════════════════════════════════════════════════════════


def setup_font():
    """
    .ttcファイルをmatplotlibに直接登録する。
    apt-getでインストールしても自動認識されない場合があるため
    addfont()でパスを明示的に指定する。
    """
    font_manager.fontManager.addfont(FONT_PATH)
    available = [f.name for f in font_manager.fontManager.ttflist]
    if FONT_NAME in available:
        plt.rcParams["font.family"] = FONT_NAME
        print(f"✅ フォント設定完了: {FONT_NAME}")
    else:
        print(f"⚠️  フォント '{FONT_NAME}' が見つかりません。")


def load_sheet(sheet_gid: str) -> pd.DataFrame:
    """
    メンバーマスターを公開URLからCSVで取得する。
    以前は別スプレッドシートのIMPORTRANGEミラーを見ていたが、
    参照が切れて中身が「#REF!」だけになり日次更新が止まったので、
    generate_members.pyと同じマスターを直接読む。
    """
    url = f"{PUB_BASE}?gid={sheet_gid}&single=true&output=csv"
    df = pd.read_csv(StringIO(fetch_text(url)), header=0, dtype=str)
    missing = [c for c in REQUIRED_COLS if c not in df.columns]
    if missing:
        raise RuntimeError(
            f"シートの列がおかしい。不足: {missing} / 取得列: {df.columns.tolist()}"
        )
    return df


def parse_date(val) -> date | None:
    """
    日付文字列をdateオブジェクトに変換する。
    空欄・NaN・パース失敗はすべてNoneを返す。
    対応フォーマット: 「2013年2月25日」「2013/02/25」「2013-02-25」
    """
    if pd.isna(val) or str(val).strip() == "":
        return None
    s = str(val).strip()
    for fmt in ("%Y年%m月%d日", "%Y/%m/%d", "%Y-%m-%d"):
        try:
            return datetime.strptime(s, fmt).date()
        except ValueError:
            continue
    print(f"  ⚠️  日付パース失敗: '{s}'")
    return None


def calc_duration(start: date, end: date) -> tuple[int, int, int]:
    """
    2つの日付から経過年数・月数・総日数を返す。
    戻り値: (years, months, total_days)
    """
    total_days = (end - start).days
    years  = end.year - start.year
    months = end.month - start.month
    if end.day < start.day:
        months -= 1
    if months < 0:
        years  -= 1
        months += 12
    return years, months, total_days


def load_members(sheet_gid: str, group_no: str = None, sort_by_sub: bool = False) -> list[dict]:
    """
    シートを読み込んでメンバーの辞書リストに変換する。
    group_noを指定するとグループNoでフィルタする。Noneなら全員。
    sort_by_subがTrueならサブIDを最優先キーにして並べる（サブグループのあるグループ用）。

    列名（マスターデータのヘッダーと一致）:
        名前, 生年月日, ハロプロ卒業日, グループ加入日,
        カラー, 旧or別カラー, 色変更日,
        リーダー就任, リーダー交代, サブリーダー就任, サブリーダー交代

    バリデーション:
        - リーダー/サブリーダーの就任日がグループ加入日より前の場合は警告してスキップ
        - 役職の交代日が空欄の場合は在籍終了日（卒業日or今日）を使う

    色変更ロジック:
        色変更日が空欄 -> 旧or別カラーは別グループ時代の色なので無視、カラーのみ使用
        色変更日に日付あり -> その日を境に旧or別カラー->カラーに切り替わる
    """
    df = load_sheet(sheet_gid)
    print(f"📋 取得列: {df.columns.tolist()}")
    print(f"📋 行数: {len(df)}")

    # グループNoでフィルタ
    if group_no:
        df = df[df["グループNo"].str.strip() == str(group_no)]
        print(f"📋 グループNo={group_no} フィルタ後行数: {len(df)}")

    today = date.today()
    members = []

    # 列名の定数（マスターデータの列名と一致させる）
    COL_NAME         = "名前"
    COL_BIRTH        = "生年月日"
    COL_GRADUATE     = "ハロプロ卒業日"
    COL_DEBUT = "グループ加入日"   # グループ別タイムライン用
    # COL_DEBUT = "デビュー日"     # ハロプロ全体タイムライン用
    COL_COLOR        = "カラー"
    COL_COLOR_PREV   = "旧or別カラー"
    COL_COLOR_CHANGE = "色変更日"
    COL_COLOR_PREV2  = "旧カラー2"
    COL_COLOR_CHANGE2= "色変更日2"
    COL_SORT_ID      = "ソートID"
    COL_SUB_ID       = "サブID"
    COL_LEADER_START = "リーダー就任"
    COL_LEADER_END   = "リーダー交代"
    COL_SUB_START    = "サブリーダー就任"
    COL_SUB_END      = "サブリーダー交代"

    for _, row in df.iterrows():
        name = str(row[COL_NAME]).strip()
        if not name or name == "nan":
            continue

        birth        = parse_date(row[COL_BIRTH])
        debut        = parse_date(row[COL_DEBUT])
        graduate     = parse_date(row[COL_GRADUATE])
        color_main    = str(row[COL_COLOR]).strip()
        color_prev    = str(row[COL_COLOR_PREV]).strip()  if COL_COLOR_PREV   in row else ""
        color_change  = parse_date(row[COL_COLOR_CHANGE]) if COL_COLOR_CHANGE  in row else None
        color_prev2   = str(row[COL_COLOR_PREV2]).strip() if COL_COLOR_PREV2  in row else ""
        color_change2 = parse_date(row[COL_COLOR_CHANGE2])if COL_COLOR_CHANGE2 in row else None
        sort_id       = float(row[COL_SORT_ID]) if COL_SORT_ID in row and pd.notna(row[COL_SORT_ID]) and str(row[COL_SORT_ID]).strip() != "" else 9999
        sub_id        = float(row[COL_SUB_ID])  if COL_SUB_ID  in row and pd.notna(row[COL_SUB_ID])  and str(row[COL_SUB_ID]).strip()  != "" else 9999

        # リーダー・サブリーダーの期間を取得
        leader_start = parse_date(row[COL_LEADER_START]) if COL_LEADER_START in row else None
        leader_end   = parse_date(row[COL_LEADER_END])   if COL_LEADER_END   in row else None
        sub_start    = parse_date(row[COL_SUB_START])    if COL_SUB_START    in row else None
        sub_end      = parse_date(row[COL_SUB_END])      if COL_SUB_END      in row else None

        if not debut:
            print(f"  ⚠️  スキップ（デビュー日なし）: {name}")
            continue

        end_date = graduate if graduate else today

        # ── バリデーション: 就任日がデビュー日より前はおかしいのでエラー ──
        if leader_start and leader_start < debut:
            print(f"  ⚠️  {name}: リーダー就任日({leader_start})がデビュー日({debut})より前のためスキップ")
            leader_start = None
            leader_end   = None
        if sub_start and sub_start < debut:
            print(f"  ⚠️  {name}: サブリーダー就任日({sub_start})がデビュー日({debut})より前のためスキップ")
            sub_start = None
            sub_end   = None

        # 交代日が空欄なら在籍終了日を使う（現任）
        if leader_start and not leader_end:
            leader_end = end_date
        if sub_start and not sub_end:
            sub_end = end_date

        # カラー未設定ならデフォルト色
        main_color = color_main if color_main and color_main != "nan" else COLOR_MISSING

        # 色変更ロジック（最大3段階）
        # 色変更日がある場合のみ旧色を有効化
        # 旧色が空欄の場合はグレー（COLOR_MISSING）をデフォルトにする
        prev_color   = None
        change_date  = None
        prev_color2  = None
        change_date2 = None
        if color_change:
            prev_color  = color_prev if color_prev and color_prev != "nan" else COLOR_MISSING
            change_date = color_change
        if color_change2:
            prev_color2  = color_prev2 if color_prev2 and color_prev2 != "nan" else COLOR_MISSING
            change_date2 = color_change2
        # 色変更日がグループ加入日より前の場合はリセット（別グループ時代の色変更）
        if change_date and debut and change_date <= debut:
            prev_color  = None
            change_date = None
        if change_date2 and debut and change_date2 <= debut:
            prev_color2  = None
            change_date2 = None

        # 色変更日がグループ加入日より前の場合はリセット（別グループ時代の色変更）
        if change_date and debut and change_date <= debut:
            prev_color  = None
            change_date = None
        if change_date2 and debut and change_date2 <= debut:
            prev_color2  = None
            change_date2 = None

        # 在籍期間・加入年齢をPythonで計算
        years, months, total_days = calc_duration(debut, end_date)
        join_age_y, join_age_m, _ = calc_duration(birth, debut) if birth else (None, None, None)

        members.append({
            "name":          name,
            "birth":         birth,
            "debut":         debut,
            "graduate":      graduate,
            "end":           end_date,
            "color":         main_color,
            "prev_color":    prev_color,
            "change_date":   change_date,
            "prev_color2":   prev_color2,
            "change_date2":  change_date2,
            "years":         years,
            "months":        months,
            "total_days":    total_days,
            "join_age_y":    join_age_y,
            "join_age_m":    join_age_m,
            "sort_id":       sort_id,
            "sub_id":        sub_id,
            "leader_start":  leader_start,
            "leader_end":    leader_end,
            "sub_start":     sub_start,
            "sub_end":       sub_end,
        })

    # デビュー日→ソートIDの順で昇順ソート（sort_by_subならサブIDを先頭に）
    if sort_by_sub:
        members.sort(key=lambda m: (m["sub_id"], m["debut"], m["sort_id"]))
    else:
        members.sort(key=lambda m: (m["debut"], m["sort_id"]))
    return members


def build_title(members: list[dict], sheet_name: str, formation: date | None = None) -> str:
    """タイトル文字列を組み立てる。formation未指定なら最初の加入日を結成日とする。"""
    formation     = formation or min(m["debut"] for m in members)
    total         = len(members)
    formation_str = f"{formation.year}年{formation.month}月{formation.day}日"
    return f"{sheet_name} 在籍タイムライン（計{total}人　結成日：{formation_str}）"


def to_year(d: date) -> float:
    """dateを小数年に変換。X軸座標に使用。"""
    return d.year + (d.timetuple().tm_yday - 1) / 365.25


def draw_timeline(members: list[dict], output_path: str, sheet_name: str,
                  sub_names: dict[int, str] | None = None,
                  formation: date | None = None):
    """
    在籍タイムラインを描画してPNGに保存する。

    レイアウト:
        左側ラベル: 「名前（大・太字）/ 加入時XX歳XXか月（小）」
        右側ラベル: 「XXXX日（大・太字）/ XX歳XXか月卒業 or 現在年齢（小）」
        役職ライン: リーダー=金、サブリーダー=銀（黒縁付き）
        白バー枠:   #ffffffのバーには黒枠を追加（白背景で見えなくなるのを防ぐ）
        現役▶:      バー右端に ▶ マーカー
        凡例:       左下に配置
    """
    setup_font()

    # ── X軸の範囲 ────────────────────────────────────────
    all_dates = [m["debut"] for m in members] + [m["end"] for m in members]
    x_min = date(min(all_dates).year, 1, 1)
    x_max = date(max(all_dates).year + 1, 1, 1)

    # ── 図サイズ ─────────────────────────────────────────
    n     = len(members)
    fig_h = n * ROW_HEIGHT + FIG_MARGIN_T + FIG_MARGIN_B

    fig, ax = plt.subplots(figsize=(FIG_WIDTH, fig_h))
    fig.patch.set_facecolor(COLOR_BG)
    ax.set_facecolor(COLOR_BG)

    # ── 各メンバーのバーとラベル ─────────────────────────
    name_texts = []   # サブグループ名の位置決めに使う
    for i, m in enumerate(members):
        y       = i
        x_start = to_year(m["debut"])
        x_end   = to_year(m["end"])

        # バー描画（色変更最大3段階対応）
        # #ffffffのバーは白背景で消えるため黒枠（edgecolor）を付ける
        is_white = m["color"].lower().replace(" ", "") in ("#ffffff", "#fff")
        edge_color = "black" if is_white else "none"
        edge_width = 0.8     if is_white else 0

        if m["change_date2"] and m["prev_color2"] and m["change_date"] and m["prev_color"]:
            # 3段階: デビュー〜変更日2を旧色2、変更日2〜変更日を旧色、変更日〜終了を新色
            x_change  = to_year(m["change_date"])
            x_change2 = to_year(m["change_date2"])
            ax.barh(y, x_change2 - x_start, left=x_start,
                    height=BAR_HEIGHT, color=m["prev_color2"],
                    align="center", linewidth=0)
            ax.barh(y, x_change - x_change2, left=x_change2,
                    height=BAR_HEIGHT, color=m["prev_color"],
                    align="center", linewidth=0)
            ax.barh(y, x_end - x_change, left=x_change,
                    height=BAR_HEIGHT, color=m["color"],
                    edgecolor=edge_color, linewidth=edge_width,
                    align="center")
        elif m["change_date"] and m["prev_color"]:
            # 2段階: デビュー〜変更日を旧色、変更日〜終了を新色
            x_change = to_year(m["change_date"])
            ax.barh(y, x_change - x_start, left=x_start,
                    height=BAR_HEIGHT, color=m["prev_color"],
                    align="center", linewidth=0)
            ax.barh(y, x_end - x_change, left=x_change,
                    height=BAR_HEIGHT, color=m["color"],
                    edgecolor=edge_color, linewidth=edge_width,
                    align="center")
        elif m["change_date2"] and m["change_date"] and m["prev_color"]:
            # 2段階(旧色2なし=グレー開始): デビュー〜変更日2グレー、変更日2〜変更日旧色、変更日〜終了新色
            x_change  = to_year(m["change_date"])
            x_change2 = to_year(m["change_date2"])
            ax.barh(y, x_change2 - x_start, left=x_start,
                    height=BAR_HEIGHT, color=COLOR_MISSING,
                    align="center", linewidth=0)
            ax.barh(y, x_change - x_change2, left=x_change2,
                    height=BAR_HEIGHT, color=m["prev_color"],
                    align="center", linewidth=0)
            ax.barh(y, x_end - x_change, left=x_change,
                    height=BAR_HEIGHT, color=m["color"],
                    edgecolor=edge_color, linewidth=edge_width,
                    align="center")
        else:
            # 色変更なし
            ax.barh(y, x_end - x_start, left=x_start,
                    height=BAR_HEIGHT, color=m["color"],
                    edgecolor=edge_color, linewidth=edge_width,
                    align="center")

        # リーダー期間: バー中心に金色の線（黒縁付き）
        if m["leader_start"]:
            xl_s = to_year(m["leader_start"])
            xl_e = to_year(m["leader_end"])
            ax.plot([xl_s, xl_e], [y, y], color="black",
                    linewidth=ROLE_LINE_W + ROLE_EDGE_W * 2,
                    solid_capstyle="butt", zorder=3)
            ax.plot([xl_s, xl_e], [y, y], color=LEADER_COLOR,
                    linewidth=ROLE_LINE_W, solid_capstyle="butt", zorder=4)

        # サブリーダー期間: バー中心に銀色の線（黒縁付き）
        if m["sub_start"]:
            xs_s = to_year(m["sub_start"])
            xs_e = to_year(m["sub_end"])
            ax.plot([xs_s, xs_e], [y, y], color="black",
                    linewidth=ROLE_LINE_W + ROLE_EDGE_W * 2,
                    solid_capstyle="butt", zorder=3)
            ax.plot([xs_s, xs_e], [y, y], color=SUBLEADER_COLOR,
                    linewidth=ROLE_LINE_W, solid_capstyle="butt", zorder=4)

        # 現役メンバー: バー右端に ▶
        if m["graduate"] is None:
            ax.plot(x_end, y, ">", color=m["color"],
                    markersize=5, clip_on=False, zorder=5)

        # ── 左ラベル: 名前（大）＋加入年齢（小） ────────
        name_texts.append(ax.text(x_start - 0.08, y - 0.16, m["name"],
                ha="right", va="center",
                fontsize=FONT_SIZE_NAME, color=COLOR_TEXT, fontweight="bold"))
        if m["join_age_y"] is not None:
            ax.text(x_start - 0.08, y + 0.2,
                    f"{m['join_age_y']}歳{m['join_age_m']}か月",
                    ha="right", va="center",
                    fontsize=FONT_SIZE_SUB, color="#666666")

        # ── 右ラベル: 在籍日数（大）＋年月（小） ────────
        ax.text(x_end + 0.08, y - 0.16,
                f"{m['total_days']:,}日",
                ha="left", va="center",
                fontsize=FONT_SIZE_NAME, color=COLOR_TEXT, fontweight="bold")
        if m["birth"] is not None:
            if m["graduate"] is not None:
                # OG: 卒業時年齢
                grad_age_y, grad_age_m, _ = calc_duration(m["birth"], m["graduate"])
                ax.text(x_end + 0.08, y + 0.2,
                        f"{grad_age_y}歳{grad_age_m}か月卒業",
                        ha="left", va="center",
                        fontsize=FONT_SIZE_SUB, color="#666666")
            else:
                # 現役: 現在の年齢
                cur_age_y, cur_age_m, _ = calc_duration(m["birth"], date.today())
                ax.text(x_end + 0.08, y + 0.2,
                        f"{cur_age_y}歳{cur_age_m}か月",
                        ha="left", va="center",
                        fontsize=FONT_SIZE_SUB, color="#666666")

    # ── 軸設定 ──────────────────────────────────────────
    ax.set_xlim(to_year(x_min), to_year(x_max))
    ax.set_ylim(-0.6, n - 0.4)
    ax.invert_yaxis()

    years = range(x_min.year, x_max.year + 1)
    ax.set_xticks(list(years))
    ax.set_xticklabels(
        [str(y) for y in years],
        color=COLOR_TEXT, fontsize=FONT_SIZE_YEAR, rotation=45, ha="right"
    )

    for y in years:
        ax.axvline(y, color=COLOR_GRID, linewidth=0.5, zorder=0)

    ax.set_yticks([])
    ax.tick_params(colors=COLOR_TEXT)
    for spine in ax.spines.values():
        spine.set_edgecolor(COLOR_SPINE)

    # ── タイトル ────────────────────────────────────────
    title = build_title(members, sheet_name, formation)
    ax.set_title(title, color=COLOR_TEXT, fontsize=FONT_SIZE_TITLE,
                 pad=10, fontweight="bold")

    # ── サブグループ: 境目に点線、各ユニットの真ん中の人の名前の左にユニット名 ──
    if sub_names:
        fig.canvas.draw()   # 名前ラベルの幅を測るため一度描画する
        to_data = ax.transData.inverted()
        units: dict = {}
        for i, m in enumerate(members):
            units.setdefault(m["sub_id"], []).append(i)
        for sid, idxs in units.items():
            if idxs[0] > 0:
                ax.axhline(idxs[0] - 0.5, color="#888888", linestyle=(0, (4, 3)),
                           linewidth=1.0, zorder=1)
            label = sub_names.get(int(sid)) if sid != 9999 else None
            if not label:
                continue
            mid = idxs[(len(idxs) - 1) // 2]
            left_px = name_texts[mid].get_window_extent().x0
            x_left = to_data.transform((left_px, 0))[0]
            ax.text(x_left - 0.12, mid, label, ha="right", va="center",
                    fontsize=FONT_SIZE_NAME, color=COLOR_SUBNAME,
                    fontweight="bold", clip_on=False)

    # ── 凡例: 文字やバーに重ならない位置を選ぶ ──────────
    # 該当者のいない役職は凡例に出さない（BEYOOOOONDSにはサブリーダーがいない等）
    legend_elements = []
    if any(m["leader_start"] for m in members):
        legend_elements.append(mpatches.Patch(color=LEADER_COLOR, label="━ リーダー期間"))
    if any(m["sub_start"] for m in members):
        legend_elements.append(mpatches.Patch(color=SUBLEADER_COLOR, label="━ サブリーダー期間"))
    legend_elements.append(mpatches.Patch(color="#aaaaaa", label="▶ = 現役メンバー"))
    place_legend(fig, ax, legend_elements)

    # ── 保存 ────────────────────────────────────────────
    plt.tight_layout()
    plt.savefig(output_path, dpi=90, bbox_inches="tight",
                facecolor=fig.get_facecolor())
    plt.close()


def place_legend(fig, ax, handles):
    """
    凡例を置く。loc="best" はテキストを避けてくれないので、
    軸内を格子状に動かして名前・日数ラベルとバーとの重なりを数え、
    重なりが無い（無ければ最小の）位置を採る。左下から順に探すので、空いていれば従来どおり左下。
    """
    kw = dict(handles=handles, facecolor="white", edgecolor=COLOR_SPINE,
              labelcolor=COLOR_TEXT, fontsize=FONT_SIZE_SUB, loc="lower left")
    fig.canvas.draw()
    renderer = fig.canvas.get_renderer()
    obstacles = [t.get_window_extent(renderer) for t in ax.texts]
    obstacles += [p.get_window_extent(renderer) for p in ax.patches]
    leg = ax.legend(**kw)
    box = leg.get_window_extent(renderer)
    ax_box = ax.get_window_extent(renderer)
    w = box.width / ax_box.width
    h = box.height / ax_box.height
    steps = 40
    best, best_hits = (0.0, 0.0), None
    for iy in range(steps + 1):
        fy = iy / steps * (1 - h)
        for ix in range(steps + 1):
            fx = ix / steps * (1 - w)
            x0 = ax_box.x0 + fx * ax_box.width
            y0 = ax_box.y0 + fy * ax_box.height
            cand = box.from_bounds(x0, y0, box.width, box.height)
            hits = sum(cand.overlaps(o) for o in obstacles)
            if best_hits is None or hits < best_hits:
                best, best_hits = (fx, fy), hits
            if hits == 0:
                break
        if best_hits == 0:
            break
    ax.legend(bbox_to_anchor=best, **kw)


# ════════════════════════════════════════════════════════
if __name__ == "__main__":
    TIMELINE_DIR.mkdir(parents=True, exist_ok=True)
    for g in GROUPS:
        print(f"\n📥 {g['name']} 読み込み中...")
        members = load_members(SHEET_GID, g["no"], sort_by_sub="sub_names" in g)
        print(f"✅ {len(members)} 名のデータを取得")
        print(f"🎨 タイムライン生成中...")
        output_path = TIMELINE_DIR / g["output"]
        draw_timeline(members, output_path, g["name"], g.get("sub_names"), g.get("formation"))
        print(f"✅ 保存完了: {output_path}")

    # 画像を全部描き終えてから更新日を書き出す（途中で落ちたら更新しない）
    # 他ページは「データ取得日」だが、ここは画像を作り直した日なので「更新日」
    jst = timezone(timedelta(hours=9))
    generated = datetime.now(jst).strftime("%Y/%m/%d")
    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<p class="last-update">更新日：{generated}</p>\n')
    print(f"✅ 保存完了: {OUTPUT_GENERATED}")
