# PHP Web Application Security Analysis

A technical deep-dive into the security posture of an internal, custom-built corporate web application ("SecureCorp"). This repository demonstrates the discovery, exploitation, and structured remediation of critical web vulnerabilities (OWASP Top 10) in a raw PHP/MySQL architecture.

## Overview

The application was analyzed through black-box and white-box methodologies to identify flaws in authentication mechanisms, input handling, and session management. After demonstrating the impact of each vulnerability, comprehensive code-level fixes were applied.

The vulnerabilities discovered include:
- **SQL Injection (SQLi):** In-band (Auth Bypass, UNION) and Blind (Error-based, Boolean).
- **Cross-Site Scripting (XSS):** Persistent/Stored and Reflected variants.
- **Cross-Site Request Forgery (CSRF):** Account takeover and profile modification.
- *Bonus: SQLi escalated to Remote Code Execution (RCE) via `INTO OUTFILE`.*

---

## 1. Authentication Bypass (SQL Injection)

The login portal dynamically evaluated un-handled user input inside the SQL authentication query. 
By injecting `'` and `#` operators, the string literal was closed and the remainder of the query (including the password check) was commented out.

**Payload:** `admin' #`

### Remediation
Migrated all dynamic queries to **Prepared Statements** (parameterised queries). By separating the SQL logic from the data payload at the protocol level, injected SQL operators are treated as harmless string variables.

---

## 2. Reflected XSS (DOM Defacement)

The Product Search functionality reflected the `?q=` user input directly into the HTML response without context-aware output encoding. 

By injecting a JavaScript payload that overwrote `document.body.innerHTML`, the entire visual structure of the application was replaced, demonstrating a trivial defacement vectors and the risk of sophisticated phishing.

**Payload:** `http://localhost/web_security/search.php?q=<script>document.body.innerHTML="<h1>HACKED</h1>"</script>`

### Remediation
Applied `htmlspecialchars()` with the `ENT_QUOTES` and `UTF-8` flags when reflecting user input. This converts `<, >, ', "` into harmless HTML entities, instructing the browser to render them visually rather than parsing them as code tags.

---

## 3. Account Takeover (CSRF)

The profile settings update form relied exclusively on the presence of the `PHPSESSID` session cookie without verifying the origin of the request. 

A malicious HTML lure page was crafted that automatically submitted a hidden POST request to the application using JavaScript. Because the browser automatically attaches the victim's session cookies to requests targeting the application domain, the profile data was silently overwritten.

### Remediation
Implemented a strong **Anti-CSRF Token** mechanism using `bin2hex(random_bytes(32))`. A unique cryptographically secure token is generated per session and embedded into forms as a hidden field. The server validates this token using a constant-time comparison (`hash_equals()`) prior to accepting state-changing operations.

---

> *Note: This repository is intended for educational demonstrations of secure coding practices and vulnerability identification. The application folder `web_security` contains the raw vulnerable code for analysis.*
