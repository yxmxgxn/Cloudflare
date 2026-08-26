#!/usr/bin/env python3
"""スプレッドシート取得の共通処理(一過性エラーのリトライ付き)"""

import time
import urllib.error
import urllib.request

# GoogleのCSV配信は自動アクセスに対して散発的に400/429/5xxを返すことがある
RETRY_STATUS = {400, 408, 429, 500, 502, 503, 504}
RETRY_WAITS = [5, 15, 30, 60]  # 日次バッチなので長めに待つ


def fetch_text(url: str, *, encoding: str = "utf-8") -> str:
    last_err = None
    for attempt, wait in enumerate([*RETRY_WAITS, None]):
        try:
            req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0"})
            with urllib.request.urlopen(req, timeout=60) as res:
                return res.read().decode(encoding)
        except urllib.error.HTTPError as e:
            if e.code not in RETRY_STATUS:
                raise
            last_err = e
        except (urllib.error.URLError, TimeoutError) as e:
            last_err = e
        if wait is None:
            break
        print(f"取得失敗({last_err})、{wait}秒後にリトライ ({attempt + 1}/{len(RETRY_WAITS)})")
        time.sleep(wait)
    raise last_err
