# 学习社区 — 后端 API（PHP + Yaf）

## 技术栈

| 层级 | 技术 |
|------|------|
| 框架 | [Yaf](https://www.php.net/manual/zh/book.yaf.php)（PECL 扩展，C 实现，高性能） |
| 语言 | PHP 8.1+ |
| 数据库 | MySQL 8.0 + PDO |
| 鉴权 | JWT（HS256，纯 PHP 实现，无需外部库） |
| 入参校验 | 内置 `Validator` 链式校验 |
| 响应格式 | `{ code, message, data }` 统一结构 |

---

## 目录结构

```
backend/
├── public/
│   ├── index.php          # 入口（Nginx/Apache 指向此处）
│   └── .htaccess          # Apache Rewrite（Nginx 见下方配置）
├── conf/
│   └── application.ini    # 数据库 / JWT / App 配置
└── application/
    ├── Bootstrap.php       # 初始化（DB、插件、CORS、禁用视图）
    ├── controllers/
    │   ├── BaseController.php
    │   ├── ErrorController.php   # 全局异常兜底
    │   ├── AuthController.php
    │   ├── TutorController.php
    │   ├── UserController.php
    │   ├── OrderController.php
    │   └── StatsController.php
    ├── models/
    │   ├── BaseModel.php
    │   ├── AdminModel.php
    │   ├── UserModel.php
    │   ├── TutorModel.php
    │   └── OrderModel.php
    ├── plugins/
    │   └── AuthPlugin.php        # JWT 路由插件
    └── library/
        ├── Db.php                # PDO 单例
        ├── Jwt.php               # HS256 JWT（无外部依赖）
        ├── Response.php          # 业务码常量
        └── Validator.php         # 链式参数校验
```

---

## 快速部署

### 1. 安装 Yaf 扩展

```bash
pecl install yaf
# php.ini 中添加：
# extension=yaf.so
# yaf.use_namespace=1
```

### 2. 修改数据库配置

编辑 `conf/application.ini`，填入实际 MySQL 连接信息。

> ⚠️ **上线前务必修改 `jwt.secret` 为随机强密钥！**

### 3. 导入数据库

```bash
mysql -u root -p learn_community < ../docs/database.sql
```

### 4. 初始化管理员账号

```sql
INSERT INTO lc_admin (username, password, real_name, role_id)
VALUES ('admin', '$2y$10$...', '超级管理员', 0);
-- password 通过 PHP password_hash('yourpassword', PASSWORD_BCRYPT) 生成
```

### 5. Web 服务器配置

**Nginx（推荐）：**

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    root /path/to/backend/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

**Apache：** `public/.htaccess` 已自动配置 Rewrite。

---

## API 文档

### 响应格式（统一）

```json
{
  "code": 0,
  "message": "操作成功",
  "data": { ... }
}
```

| code | 含义 |
|------|------|
| 0    | 成功 |
| 400  | 参数错误 |
| 401  | 未登录 / Token 过期 |
| 403  | 无权限 |
| 404  | 资源不存在 |
| 500  | 服务器内部错误 |

---

### 登录鉴权

除 `POST /auth/login` 外，所有接口均需携带 Token：

```
Authorization: Bearer <token>
```

| 方法   | 路径          | 说明 |
|--------|---------------|------|
| POST   | /auth/login   | 管理员登录，返回 JWT Token |
| GET    | /auth/info    | 获取当前登录管理员信息 |
| POST   | /auth/logout  | 退出（无状态，客户端清除 Token） |

**登录请求示例：**

```bash
curl -X POST http://api.yourdomain.com/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"your_password"}'
```

---

### 陪伴师管理

| 方法   | 路径                     | 说明 |
|--------|--------------------------|------|
| GET    | /tutor/list              | 陪伴师列表（`?audit_status=&keyword=&page=&limit=`） |
| GET    | /tutor/detail            | 陪伴师详情（`?id=`）|
| GET    | /tutor/auditList         | 审核申请列表（`?status=0&page=&limit=`） |
| GET    | /tutor/auditDetail       | 审核申请详情（`?audit_id=`） |
| POST   | /tutor/audit             | 审核操作 `{"audit_id":1,"action":"pass"|"reject","remark":""}` |
| POST   | /tutor/statusChange      | 上线/下线 `{"tutor_id":1,"status":1}` |

`audit_status` / `status`：0 待审核，1 通过，2 拒绝

---

### 用户管理

| 方法   | 路径          | 说明 |
|--------|---------------|------|
| GET    | /user/list    | 用户列表（`?keyword=&status=&user_type=&page=&limit=`） |
| GET    | /user/detail  | 用户详情（`?id=`） |
| POST   | /user/status  | 启用/禁用 `{"user_id":1,"status":0}` |
| GET    | /user/orders  | 用户订单列表（`?user_id=&page=&limit=`） |

---

### 订单管理

| 方法   | 路径           | 说明 |
|--------|----------------|------|
| GET    | /order/list    | 订单列表（`?order_status=&pay_status=&keyword=&date_start=&date_end=&page=&limit=`） |
| GET    | /order/detail  | 订单详情（`?id=`） |
| POST   | /order/cancel  | 取消订单 `{"order_id":1,"reason":"原因"}` |
| POST   | /order/refund  | 批准退款 `{"order_id":1,"remark":"备注"}` |
| GET    | /order/stats   | 订单状态统计 |

`order_status`：0 待确认，1 已确认，2 服务中，3 已完成，4 已取消，5 申请退款，6 已退款

---

### 数据统计

| 方法   | 路径               | 说明 |
|--------|--------------------|------|
| GET    | /stats/dashboard   | 总览面板（用户/陪伴师/订单/营收） |
| GET    | /stats/trend       | 趋势图（`?type=order|revenue&days=7`） |
| GET    | /stats/tutorRank   | 陪伴师接单排行（`?limit=10`） |

---

## 安全说明

1. **JWT Secret**：生产环境请替换 `conf/application.ini` 中的 `jwt.secret`，使用 `openssl rand -base64 64` 生成。
2. **数据库凭证**：不要提交真实密码到版本控制，建议使用环境变量覆盖。
3. **密码哈希**：使用 PHP `password_hash()` BCrypt，绝不明文存储。
4. **SQL 注入**：所有查询均使用 PDO Prepared Statement，防止注入。
5. **异常信息**：`APP_DEBUG = false` 时，错误信息不会暴露到前端。
