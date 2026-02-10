import sys
import urllib.request
from html.parser import HTMLParser
from urllib.parse import urljoin

DEFAULT_URL = "http://127.0.0.1:8001/"

class StylesheetParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.stylesheets = []

    def handle_starttag(self, tag, attrs):
        if tag.lower() != "link":
            return
        attr_dict = dict(attrs)
        if attr_dict.get("rel", "").lower() == "stylesheet" and "href" in attr_dict:
            self.stylesheets.append(attr_dict["href"])


def fetch(url):
    req = urllib.request.Request(url, headers={"User-Agent": "css-checker"})
    with urllib.request.urlopen(req, timeout=10) as resp:
        content = resp.read()
        return resp.getcode(), resp.headers, content


def main():
    base_url = sys.argv[1] if len(sys.argv) > 1 else DEFAULT_URL

    try:
        status, headers, html = fetch(base_url)
    except Exception as exc:
        print(f"FAIL: Cannot fetch {base_url}: {exc}")
        sys.exit(2)

    if status != 200:
        print(f"FAIL: {base_url} returned status {status}")
        sys.exit(2)

    parser = StylesheetParser()
    parser.feed(html.decode("utf-8", errors="ignore"))

    if not parser.stylesheets:
        print("FAIL: No stylesheets found in HTML")
        sys.exit(2)

    all_ok = True
    print(f"Found {len(parser.stylesheets)} stylesheet link(s)")

    for href in parser.stylesheets:
        css_url = urljoin(base_url, href)
        try:
            css_status, css_headers, css_body = fetch(css_url)
            size = len(css_body)
            content_type = css_headers.get("Content-Type", "")
            ok = css_status == 200 and size > 0
            if ok:
                print(f"OK  : {css_url} ({size} bytes, {content_type})")
            else:
                print(f"FAIL: {css_url} status={css_status} size={size} type={content_type}")
                all_ok = False
        except Exception as exc:
            print(f"FAIL: {css_url} error={exc}")
            all_ok = False

    # Extra sanity check: traditional path
    try:
        css_status, _, _ = fetch(urljoin(base_url, "/assets/css/main.css"))
        print(f"INFO: /assets/css/main.css returned status {css_status}")
    except Exception as exc:
        print(f"INFO: /assets/css/main.css error={exc}")

    sys.exit(0 if all_ok else 1)


if __name__ == "__main__":
    main()
