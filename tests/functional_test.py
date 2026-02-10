import io
import re
import uuid
import unittest
from http.cookiejar import CookieJar
from urllib.parse import urlencode
from urllib.request import Request, build_opener, HTTPCookieProcessor

BASE_URL = "http://127.0.0.1:8001"


def extract_csrf_token(html: str) -> str:
    match = re.search(r'name="_csrf_token" value="([^"]+)"', html)
    if not match:
        raise AssertionError("CSRF token not found")
    return match.group(1)


def build_multipart(fields, files):
    boundary = "----MedilabBoundary" + uuid.uuid4().hex
    body = io.BytesIO()

    for name, value in fields.items():
        body.write(f"--{boundary}\r\n".encode())
        body.write(f"Content-Disposition: form-data; name=\"{name}\"\r\n\r\n".encode())
        body.write(str(value).encode())
        body.write(b"\r\n")

    for name, filename, content_type, data in files:
        body.write(f"--{boundary}\r\n".encode())
        body.write(
            f"Content-Disposition: form-data; name=\"{name}\"; filename=\"{filename}\"\r\n".encode()
        )
        body.write(f"Content-Type: {content_type}\r\n\r\n".encode())
        body.write(data)
        body.write(b"\r\n")

    body.write(f"--{boundary}--\r\n".encode())

    return boundary, body.getvalue()


class MedilabBackendTest(unittest.TestCase):
    def setUp(self):
        self.cookie_jar = CookieJar()
        self.opener = build_opener(HTTPCookieProcessor(self.cookie_jar))

    def get(self, path):
        request = Request(f"{BASE_URL}{path}")
        with self.opener.open(request) as response:
            return response.getcode(), response.read().decode("utf-8")

    def post(self, path, data=None, headers=None):
        if headers is None:
            headers = {}
        encoded = urlencode(data or {}).encode("utf-8")
        request = Request(f"{BASE_URL}{path}", data=encoded, headers=headers)
        with self.opener.open(request) as response:
            return response.getcode(), response.read().decode("utf-8")

    def post_multipart(self, path, fields, files):
        boundary, body = build_multipart(fields, files)
        headers = {
            "Content-Type": f"multipart/form-data; boundary={boundary}",
            "Content-Length": str(len(body)),
        }
        request = Request(f"{BASE_URL}{path}", data=body, headers=headers)
        with self.opener.open(request) as response:
            return response.getcode(), response.read().decode("utf-8")

    def test_01_register_and_login_patient(self):
        code, html = self.get("/register")
        self.assertEqual(code, 200)
        csrf = extract_csrf_token(html)

        unique = uuid.uuid4().hex[:8]
        username = f"patient_{unique}"
        email = f"{username}@example.com"

        code, _ = self.post(
            "/register",
            data={
                "_csrf_token": csrf,
                "username": username,
                "email": email,
                "password": "Test12345!",
                "role": "patient",
                "region": "tunis",
            },
        )
        self.assertIn(code, [200, 302])

        code, html = self.get("/login")
        self.assertEqual(code, 200)
        csrf = extract_csrf_token(html)

        code, _ = self.post(
            "/login",
            data={"_csrf_token": csrf, "username": username, "password": "Test12345!"},
        )
        self.assertIn(code, [200, 302])

        code, html = self.get("/dashboard/patient")
        self.assertIn(code, [200, 302])
        self.assertIn("Patient Dashboard", html)

    def test_02_register_doctor_and_upload(self):
        code, html = self.get("/register")
        self.assertEqual(code, 200)
        csrf = extract_csrf_token(html)

        unique = uuid.uuid4().hex[:8]
        username = f"doctor_{unique}"
        email = f"{username}@example.com"

        code, _ = self.post(
            "/register",
            data={
                "_csrf_token": csrf,
                "username": username,
                "email": email,
                "password": "Test12345!",
                "role": "doctor",
                "region": "tunis",
            },
        )
        self.assertIn(code, [200, 302])

        code, html = self.get("/login")
        self.assertEqual(code, 200)
        csrf = extract_csrf_token(html)

        code, _ = self.post(
            "/login",
            data={"_csrf_token": csrf, "username": username, "password": "Test12345!"},
        )
        self.assertIn(code, [200, 302])

        code, html = self.get("/doctor/verification")
        self.assertEqual(code, 200)
        csrf = extract_csrf_token(html)

        pdf_bytes = b"%PDF-1.4\n%\xe2\xe3\xcf\xd3\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF"

        code, _ = self.post_multipart(
            "/doctor/verification/submit",
            fields={"_csrf_token": csrf, "license_number": "DOC-123456"},
            files=[("certification_pdf", "license.pdf", "application/pdf", pdf_bytes)],
        )
        self.assertIn(code, [200, 302])

        code, html = self.get("/admin/doctor-verifications")
        self.assertIn(code, [200, 302, 403])
        if code == 200:
            self.assertIn("Welcome Back", html)


if __name__ == "__main__":
    print("Running Medilab backend endpoint tests...")
    print("Ensure Symfony server is running at", BASE_URL)
    unittest.main()
