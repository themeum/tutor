# Utilities

> Security helpers, nonce utilities, and other standalone functions.

---

## Security Utilities

**File:** [`ts/utils/security.ts`](../ts/utils/security.ts) &nbsp;|&nbsp; **Access:** `TutorCore.security`

### `escapeHtml(unsafeText: string): string`

Escape HTML entities to prevent XSS when inserting user-generated content into the DOM.

**Implementation:** Uses `element.innerText` → `element.innerHTML` to leverage the browser's native escaping.

```javascript
const safe = TutorCore.security.escapeHtml('<script>alert("xss")</script>');
// → '&lt;script&gt;alert("xss")&lt;/script&gt;'
```

### `escapeAttr(str: string): string`

Escape a string for safe use in HTML attribute values.

**Characters escaped:** `&`, `"`, `'`, `<`, `>`

```javascript
const safe = TutorCore.security.escapeAttr('John "the dev" O\'Brien');
// → 'John &quot;the dev&quot; &#039;Brien'
```

---

## Nonce Utilities

**File:** [`ts/utils/nonce.ts`](../ts/utils/nonce.ts) &nbsp;|&nbsp; **Access:** `TutorCore.nonce`

### `getNonceData(sendKeyValue?: boolean): NonceData | NonceKeyValue`

Retrieve WordPress nonce data from the global `window._tutorobject` for authenticated AJAX requests.

**Modes:**

| Argument          | Return shape       | Example                                    |
| ----------------- | ------------------ | ------------------------------------------ |
| `false` / omitted | `{ [key]: value }` | `{ '_tutor_nonce': 'abc123' }`             |
| `true`            | `{ key, value }`   | `{ key: '_tutor_nonce', value: 'abc123' }` |

### Usage

```javascript
// As query params
const nonceParams = TutorCore.nonce.getNonceData();
// { '_tutor_nonce': 'abc123' }

// As key-value pair
const { key, value } = TutorCore.nonce.getNonceData(true);
// key = '_tutor_nonce', value = 'abc123'

// Include in fetch request
const formData = new FormData();
const nonce = TutorCore.nonce.getNonceData();
Object.entries(nonce).forEach(([k, v]) => formData.append(k, v));
```
