# 中小学生青少年学习社区 — 完整接口文档

> **版本**：v1.0.0　**更新时间**：2026-04-16　**Base URL**：`https://api.yourdomain.com`

---

## 目录

1. [全局约定](#1-全局约定)
2. [鉴权模块](#2-鉴权模块)
3. [用户模块](#3-用户模块)
4. [陪伴师模块](#4-陪伴师模块)
5. [资质审核模块](#5-资质审核模块)
6. [订单模块](#6-订单模块)
7. [社区模块](#7-社区模块)
8. [自习模块](#8-自习模块)
9. [家长监护模块](#9-家长监护模块)
10. [数据统计模块（管理端）](#10-数据统计模块管理端)
11. [附录：枚举常量速查](#11-附录枚举常量速查)

---

## 1 全局约定

### 1.1 请求规范

| 项目 | 说明 |
|------|------|
| 协议 | HTTPS |
| 请求格式 | `Content-Type: application/json`（POST / PUT），GET 参数放 QueryString |
| 字符集 | UTF-8 |
| 鉴权方式 | `Authorization: Bearer <token>` 请求头，或 `X-Token: <token>` |
| 时间格式 | ISO 8601：`2024-08-01 12:00:00` |

### 1.2 统一响应格式

所有接口均返回以下 JSON 结构：

```json
{
  "code":    0,
  "message": "操作成功",
  "data":    { }
}
```

| 字段 | 类型 | 说明 |
|------|------|------|
| code | int | 业务状态码，0 = 成功，其余见错误码表 |
| message | string | 可直接展示的提示文字 |
| data | object \| array \| null | 业务数据，失败时为 null |

### 1.3 错误码

| code | HTTP 状态码 | 含义 |
|------|-------------|------|
| 0 | 200 | 成功 |
| 400 | 200 | 参数错误（具体原因见 message） |
| 401 | 200 | 未登录 / Token 已过期 |
| 403 | 200 | 无操作权限 |
| 404 | 200 | 资源不存在 |
| 500 | 200 | 服务器内部错误 |

> HTTP 状态码统一返回 200，业务错误通过 `code` 字段区分。

### 1.4 分页约定

所有分页接口的 data 均包含：

```json
{
  "list":  [...],
  "total": 128,
  "page":  1,
  "limit": 20,
  "pages": 7
}
```

| 参数 | 类型 | 默认 | 说明 |
|------|------|------|------|
| page | int | 1 | 当前页码，最大 10000 |
| limit | int | 20 | 每页条数，最大 100 |

---

## 2 鉴权模块

### 2.1 小程序用户登录（微信授权）

**POST** `/user/wxLogin`

> 小程序调用 `wx.login` 获取 code，传给后端换取用户 Token。

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| code | string | ✅ | `wx.login` 返回的临时 code |
| nickname | string | ❌ | 用户昵称（首次注册时传入） |
| avatar | string | ❌ | 头像 URL（首次注册时传入） |
| user_type | int | ❌ | 用户类型：1 学生 2 家长（默认 1） |

**返回数据**

```json
{
  "code": 0,
  "message": "登录成功",
  "data": {
    "token":   "eyJhbGciOiJIUzI1NiJ9...",
    "expires": 1754000000,
    "is_new":  false,
    "user": {
      "id":           1001,
      "openid":       "oXXXXXX",
      "nickname":     "小明同学",
      "avatar":       "https://cdn.example.com/avatar/1001.jpg",
      "real_name":    "",
      "mobile":       "",
      "gender":       0,
      "user_type":    1,
      "school":       "",
      "grade":        "",
      "balance":      "0.00",
      "total_study_min": 0,
      "status":       1
    }
  }
}
```

---

### 2.2 管理端登录

**POST** `/auth/login`

> 管理后台账号密码登录（无需 Token）。

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| username | string | ✅ | 登录账号 |
| password | string | ✅ | 登录密码（≥ 6 位） |

**返回数据**

```json
{
  "code": 0,
  "message": "登录成功",
  "data": {
    "token":   "eyJhbGciOiJIUzI1NiJ9...",
    "expires": 1754000000,
    "admin": {
      "id":        1,
      "username":  "admin",
      "real_name": "超级管理员",
      "avatar":    "",
      "role_id":   1
    }
  }
}
```

---

### 2.3 获取当前登录者信息

**GET** `/auth/info`

**请求头**：`Authorization: Bearer <token>`

**返回数据**

```json
{
  "code": 0,
  "message": "操作成功",
  "data": {
    "id":           1,
    "username":     "admin",
    "real_name":    "超级管理员",
    "avatar":       "",
    "email":        "",
    "mobile":       "",
    "role_id":      1,
    "status":       1,
    "last_login_at":"2024-08-01 10:00:00"
  }
}
```

---

### 2.4 退出登录

**POST** `/auth/logout`

> JWT 无状态，服务端不维护黑名单；前端清除本地 Token 即完成退出。本接口供前端统一调用。

**返回数据**

```json
{ "code": 0, "message": "已退出登录", "data": null }
```

---

## 3 用户模块

### 3.1 获取用户信息

**GET** `/user/info`

**返回数据**

```json
{
  "code": 0,
  "message": "操作成功",
  "data": {
    "id":             1001,
    "nickname":       "小明同学",
    "avatar":         "https://cdn.example.com/avatar/1001.jpg",
    "real_name":      "张小明",
    "mobile":         "138****8888",
    "gender":         1,
    "birthday":       "2010-06-15",
    "school":         "北京市第一中学",
    "grade":          "初三",
    "user_type":      1,
    "status":         1,
    "balance":        "12.50",
    "total_study_min":3600,
    "last_login_at":  "2024-08-01 10:00:00",
    "create_time":    "2024-01-01 09:00:00"
  }
}
```

---

### 3.2 修改用户信息

**POST** `/user/update`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| nickname | string | ❌ | 昵称（≤ 32 字符） |
| avatar | string | ❌ | 头像 URL |
| real_name | string | ❌ | 真实姓名（≤ 20 字符） |
| gender | int | ❌ | 性别：0 未知 1 男 2 女 |
| birthday | string | ❌ | 生日，格式 `YYYY-MM-DD` |
| school | string | ❌ | 学校名称（≤ 128 字符） |
| grade | string | ❌ | 年级，如"初三"（≤ 32 字符） |

**返回数据**

```json
{ "code": 0, "message": "修改成功", "data": null }
```

---

### 3.3 绑定手机号

**POST** `/user/bindMobile`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| mobile | string | ✅ | 11 位手机号 |
| code | string | ✅ | 短信验证码 |

**返回数据**

```json
{ "code": 0, "message": "手机号绑定成功", "data": null }
```

---

### 3.4 钱包余额

**GET** `/user/wallet`

**返回数据**

```json
{
  "code": 0,
  "message": "操作成功",
  "data": {
    "balance":  "88.50",
    "frozen":   "0.00",
    "total_in": "200.00",
    "total_out":"111.50"
  }
}
```

---

### 3.5 钱包流水列表

**GET** `/user/walletLogs`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| log_type | int | ❌ | 流水类型（见附录） |
| page | int | ❌ | 页码，默认 1 |
| limit | int | ❌ | 每页条数，默认 20 |

**返回数据（list 元素）**

```json
{
  "id":             101,
  "amount":         "-30.00",
  "before_balance": "118.50",
  "after_balance":  "88.50",
  "log_type":       2,
  "log_type_label": "消费",
  "remark":         "支付订单 LC20240801001",
  "create_time":    "2024-08-01 14:00:00"
}
```

---

### 3.6 消息通知列表

**GET** `/user/notifications`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| is_read | int | ❌ | 0 未读，1 已读，不传查全部 |
| page | int | ❌ | 页码 |
| limit | int | ❌ | 每页条数 |

**返回数据（list 元素）**

```json
{
  "id":          201,
  "title":       "订单已确认",
  "content":     "您的订单 LC20240801001 已被陪伴师确认，请按时参加。",
  "notify_type": 2,
  "is_read":     0,
  "create_time": "2024-08-01 15:00:00"
}
```

---

### 3.7 标记通知已读

**POST** `/user/readNotification`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| ids | array | ❌ | 通知 ID 数组，不传则全部标为已读 |

**返回数据**

```json
{ "code": 0, "message": "操作成功", "data": null }
```

---

### 3.8 用户列表（管理端）

**GET** `/user/list`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| keyword | string | ❌ | 搜索：昵称 / 真实姓名 / 手机号 |
| status | int | ❌ | 状态：0 禁用，1 正常 |
| user_type | int | ❌ | 1 学生，2 家长 |
| page | int | ❌ | 页码 |
| limit | int | ❌ | 每页条数 |

**返回数据（list 元素）**

```json
{
  "id":           1001,
  "nickname":     "小明同学",
  "real_name":    "张小明",
  "mobile":       "138****8888",
  "avatar":       "https://cdn.example.com/avatar/1001.jpg",
  "user_type":    1,
  "status":       1,
  "balance":      "12.50",
  "total_study_min": 3600,
  "last_login_at":"2024-08-01 10:00:00",
  "create_time":  "2024-01-01 09:00:00"
}
```

---

### 3.9 用户详情（管理端）

**GET** `/user/detail?id=1001`

**返回数据**

> 同 3.1 `/user/info`，额外含 `last_login_ip`、`register_source` 字段。

---

### 3.10 启用 / 禁用用户（管理端）

**POST** `/user/status`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| user_id | int | ✅ | 用户 ID |
| status | int | ✅ | 0 禁用，1 启用 |

**返回数据**

```json
{ "code": 0, "message": "用户已禁用", "data": null }
```

---

## 4 陪伴师模块

### 4.1 提交入驻申请（小程序端）

**POST** `/tutor/apply`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| real_name | string | ✅ | 真实姓名（≤ 20 字符） |
| mobile | string | ✅ | 联系手机号 |
| gender | int | ✅ | 性别：1 男 2 女 |
| age | int | ✅ | 年龄（16–60） |
| education | int | ✅ | 学历（见附录） |
| school | string | ✅ | 毕业院校（≤ 128 字符） |
| major | string | ❌ | 专业（≤ 64 字符） |
| subjects | string | ✅ | 擅长科目，逗号分隔，如 `"数学,物理"` |
| introduction | string | ❌ | 个人简介（≤ 500 字符） |
| service_price | number | ✅ | 服务单价（元/小时，> 0） |
| service_type | int | ✅ | 服务类型（见附录） |
| cert_file_ids | array | ✅ | 已上传证件文件 ID 列表（至少 1 个） |

**返回数据**

```json
{
  "code": 0,
  "message": "申请已提交，等待审核",
  "data": {
    "tutor_id":   50,
    "audit_id":   12,
    "audit_status": 0
  }
}
```

---

### 4.2 查询我的申请状态（小程序端）

**GET** `/tutor/myApply`

**返回数据**

```json
{
  "code": 0,
  "message": "操作成功",
  "data": {
    "tutor_id":    50,
    "audit_status":1,
    "audit_remark":"资质齐全，审核通过",
    "audit_at":    "2024-08-02 10:00:00",
    "status":      1,
    "real_name":   "李晓华",
    "subjects":    "数学,物理",
    "service_price":"60.00"
  }
}
```

`audit_status`：0 待审核，1 通过，2 拒绝

---

### 4.3 陪伴师列表（管理端）

**GET** `/tutor/list`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| audit_status | int | ❌ | 审核状态：0/1/2 |
| status | int | ❌ | 上线状态：0 下线，1 上线 |
| keyword | string | ❌ | 搜索：真实姓名 / 手机号 |
| page | int | ❌ | 页码 |
| limit | int | ❌ | 每页条数 |

**返回数据（list 元素）**

```json
{
  "id":           50,
  "user_id":      1001,
  "real_name":    "李晓华",
  "mobile":       "139****9999",
  "avatar":       "https://cdn.example.com/avatar/50.jpg",
  "education":    3,
  "school":       "北京大学",
  "service_price":"60.00",
  "audit_status": 1,
  "status":       1,
  "score":        "4.9",
  "order_count":  128,
  "total_income": "7680.00",
  "create_time":  "2024-06-01 08:00:00",
  "nickname":     "学霸辅导员"
}
```

---

### 4.4 陪伴师详情（管理端）

**GET** `/tutor/detail?id=50`

**返回数据**（在列表字段基础上追加）

```json
{
  "...":            "...",
  "major":          "理论物理",
  "subjects":       "数学,物理",
  "introduction":   "北京大学理论物理在读研究生...",
  "service_type":   1,
  "commission_rate":"0.80",
  "audit_remark":   "资质齐全",
  "audit_at":       "2024-06-02 10:00:00",
  "cert_files": [
    {
      "id":        1,
      "file_type": 1,
      "file_url":  "https://cdn.example.com/cert/1.jpg",
      "file_name": "身份证正面.jpg",
      "create_time":"2024-06-01 08:00:00"
    }
  ],
  "latest_audit": {
    "id":         12,
    "audit_type": 1,
    "status":     1,
    "remark":     "资质齐全，审核通过",
    "admin_id":   1,
    "audit_at":   "2024-06-02 10:00:00",
    "create_time":"2024-06-01 08:00:00"
  }
}
```

---

### 4.5 上线 / 下线陪伴师（管理端）

**POST** `/tutor/statusChange`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| tutor_id | int | ✅ | 陪伴师 ID |
| status | int | ✅ | 0 下线，1 上线 |

**返回数据**

```json
{ "code": 0, "message": "陪伴师已上线", "data": null }
```

---

### 4.6 证件文件上传

**POST** `/cert/upload`

> 先上传文件获取 file_id，再通过 `/tutor/apply` 传入 cert_file_ids。

**请求参数**（multipart/form-data）

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| file | binary | ✅ | 图片文件（jpg/png，≤ 5 MB） |
| file_type | int | ✅ | 文件类型（见附录） |

**返回数据**

```json
{
  "code": 0,
  "message": "上传成功",
  "data": {
    "file_id":   1,
    "file_url":  "https://cdn.example.com/cert/1.jpg",
    "file_name": "身份证正面.jpg",
    "file_size": 245760,
    "mime_type": "image/jpeg"
  }
}
```

---

## 5 资质审核模块

### 5.1 审核申请列表（管理端）

**GET** `/tutor/auditList`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| status | int | ❌ | 审核状态：0 待审核，1 通过，2 拒绝（默认 0） |
| page | int | ❌ | 页码 |
| limit | int | ❌ | 每页条数 |

**返回数据（list 元素）**

```json
{
  "id":            12,
  "tutor_id":      50,
  "audit_type":    1,
  "audit_type_label":"入驻申请",
  "status":        0,
  "status_label":  "待审核",
  "remark":        "",
  "admin_id":      0,
  "audit_at":      null,
  "create_time":   "2024-06-01 08:00:00",
  "real_name":     "李晓华",
  "mobile":        "139****9999",
  "education":     3,
  "school":        "北京大学",
  "user_nickname": "学霸辅导员"
}
```

---

### 5.2 审核申请详情（管理端）

**GET** `/tutor/auditDetail?audit_id=12`

**返回数据**（在列表字段基础上追加）

```json
{
  "...":        "...",
  "submit_data": {
    "real_name":    "李晓华",
    "mobile":       "13900009999",
    "education":    3,
    "school":       "北京大学",
    "major":        "理论物理",
    "subjects":     "数学,物理",
    "service_price":"60.00",
    "cert_file_ids":[1, 2, 3]
  }
}
```

---

### 5.3 审核操作（管理端）

**POST** `/tutor/audit`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| audit_id | int | ✅ | 审核记录 ID |
| action | string | ✅ | `pass`（通过）或 `reject`（拒绝） |
| remark | string | 拒绝必填 | 审核意见（≤ 255 字符） |

**返回数据**

```json
{ "code": 0, "message": "审核通过，陪伴师已上线", "data": null }
```

**错误场景**

| code | message |
|------|---------|
| 404 | 审核记录不存在 |
| 400 | 该申请已处理，不可重复审核 |
| 400 | 拒绝时必须填写审核意见 |

---

## 6 订单模块

### 6.1 创建订单（小程序端）

**POST** `/order/create`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| tutor_id | int | ✅ | 陪伴师 ID |
| service_type | int | ✅ | 服务类型（见附录） |
| service_date | string | ✅ | 预约日期，格式 `YYYY-MM-DD` |
| service_start | string | ✅ | 开始时间，格式 `HH:MM:SS` |
| service_end | string | ✅ | 结束时间，格式 `HH:MM:SS` |
| remark | string | ❌ | 用户备注（≤ 500 字符） |

**返回数据**

```json
{
  "code": 0,
  "message": "订单创建成功",
  "data": {
    "order_id":  301,
    "order_no":  "LC20240801001",
    "amount":    "120.00",
    "pay_amount":"120.00",
    "service_minutes": 120,
    "order_status": 0,
    "pay_status":   0
  }
}
```

---

### 6.2 订单支付（小程序端）

**POST** `/order/pay`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| order_id | int | ✅ | 订单 ID |
| pay_type | int | ✅ | 支付方式：1 微信支付，2 余额支付 |

**返回数据（微信支付）**

```json
{
  "code": 0,
  "message": "操作成功",
  "data": {
    "pay_type": 1,
    "wx_prepay": {
      "timeStamp": "1722470000",
      "nonceStr":  "XXXX",
      "package":   "prepay_id=wx...",
      "signType":  "MD5",
      "paySign":   "XXXXX"
    }
  }
}
```

**返回数据（余额支付）**

```json
{ "code": 0, "message": "支付成功", "data": { "pay_type": 2 } }
```

---

### 6.3 订单列表（小程序端）

**GET** `/order/myList`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| order_status | int | ❌ | 订单状态筛选（见附录） |
| page | int | ❌ | 页码 |
| limit | int | ❌ | 每页条数 |

**返回数据（list 元素）**

```json
{
  "id":           301,
  "order_no":     "LC20240801001",
  "tutor_id":     50,
  "tutor_name":   "李晓华",
  "tutor_avatar": "https://cdn.example.com/avatar/50.jpg",
  "service_type": 1,
  "service_date": "2024-08-05",
  "service_start":"10:00:00",
  "service_end":  "12:00:00",
  "service_minutes":120,
  "amount":       "120.00",
  "pay_amount":   "120.00",
  "pay_status":   1,
  "order_status": 3,
  "create_time":  "2024-08-01 14:00:00"
}
```

---

### 6.4 订单详情

**GET** `/order/detail?id=301`

**返回数据**（在列表字段基础上追加）

```json
{
  "...": "...",
  "discount_amount":  "0.00",
  "pay_type":         1,
  "pay_time":         "2024-08-01 14:05:00",
  "wx_trade_no":      "420000...",
  "remark":           "",
  "cancel_reason":    "",
  "finish_time":      "2024-08-05 12:10:00",
  "user_nickname":    "小明同学",
  "user_mobile":      "138****8888",
  "review": {
    "id":          401,
    "score":       5,
    "content":     "陪伴师非常耐心，讲解清楚！",
    "is_anonymous":0,
    "create_time": "2024-08-05 13:00:00"
  }
}
```

---

### 6.5 取消订单（小程序端）

**POST** `/order/cancel`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| order_id | int | ✅ | 订单 ID |
| reason | string | ✅ | 取消原因（≤ 255 字符） |

**返回数据**

```json
{ "code": 0, "message": "订单已取消", "data": null }
```

---

### 6.6 申请退款（小程序端）

**POST** `/order/applyRefund`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| order_id | int | ✅ | 订单 ID |
| reason | string | ✅ | 退款原因（≤ 255 字符） |

**返回数据**

```json
{ "code": 0, "message": "退款申请已提交，等待审核", "data": null }
```

---

### 6.7 批准退款（管理端）

**POST** `/order/refund`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| order_id | int | ✅ | 订单 ID |
| remark | string | ❌ | 审核备注 |

**返回数据**

```json
{ "code": 0, "message": "退款处理成功", "data": null }
```

---

### 6.8 强制取消订单（管理端）

**POST** `/order/adminCancel`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| order_id | int | ✅ | 订单 ID |
| reason | string | ✅ | 取消原因（≤ 255 字符） |

**返回数据**

```json
{ "code": 0, "message": "订单已取消", "data": null }
```

---

### 6.9 订单列表（管理端）

**GET** `/order/list`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| order_status | int | ❌ | 订单状态 |
| pay_status | int | ❌ | 支付状态：0 待支付，1 已支付，2 已退款 |
| keyword | string | ❌ | 订单号 / 用户手机号 / 用户昵称 |
| date_start | string | ❌ | 下单开始日期 `YYYY-MM-DD` |
| date_end | string | ❌ | 下单结束日期 `YYYY-MM-DD` |
| page | int | ❌ | 页码 |
| limit | int | ❌ | 每页条数 |

---

### 6.10 提交订单评价（小程序端）

**POST** `/order/review`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| order_id | int | ✅ | 订单 ID（需已完成） |
| score | int | ✅ | 评分：1–5 |
| content | string | ❌ | 评价内容（≤ 500 字符） |
| images | array | ❌ | 评价图片 URL 数组（≤ 6 张） |
| is_anonymous | int | ❌ | 是否匿名：0 否，1 是（默认 0） |

**返回数据**

```json
{ "code": 0, "message": "评价成功", "data": null }
```

---

## 7 社区模块

### 7.1 话题列表

**GET** `/community/topics`

**返回数据（list 元素）**

```json
{
  "id":          1,
  "name":        "学习分享",
  "icon":        "https://cdn.example.com/topic/share.png",
  "description": "分享学习方法与心得",
  "post_count":  3821,
  "sort":        10
}
```

---

### 7.2 帖子列表

**GET** `/community/posts`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| topic_id | int | ❌ | 话题 ID 过滤 |
| post_type | int | ❌ | 帖子类型：1 普通动态，2 问答，3 经验分享 |
| keyword | string | ❌ | 搜索关键词（标题 / 内容） |
| sort | string | ❌ | 排序：`latest`（最新），`hot`（最热，按互动量），默认 `latest` |
| page | int | ❌ | 页码 |
| limit | int | ❌ | 每页条数 |

**返回数据（list 元素）**

```json
{
  "id":           1001,
  "user_id":      1001,
  "nickname":     "小明同学",
  "avatar":       "https://cdn.example.com/avatar/1001.jpg",
  "title":        "我的高效学习法",
  "content":      "今天分享一个番茄钟学习法...",
  "images":       ["https://cdn.example.com/post/1.jpg"],
  "post_type":    1,
  "topic_id":     1,
  "topic_name":   "学习分享",
  "like_count":   42,
  "comment_count":8,
  "view_count":   320,
  "is_top":       0,
  "is_essence":   0,
  "is_liked":     false,
  "create_time":  "2024-08-01 09:00:00"
}
```

---

### 7.3 帖子详情

**GET** `/community/postDetail?id=1001`

**返回数据**（同帖子列表元素，额外包含评论数据见 7.4）

---

### 7.4 帖子评论列表

**GET** `/community/comments?post_id=1001`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| post_id | int | ✅ | 帖子 ID |
| page | int | ❌ | 页码 |
| limit | int | ❌ | 每页条数，默认 20 |

**返回数据（list 元素）**

```json
{
  "id":          5001,
  "user_id":     1002,
  "nickname":    "学霸小王",
  "avatar":      "https://cdn.example.com/avatar/1002.jpg",
  "content":     "写得很好！",
  "like_count":  3,
  "is_liked":    false,
  "parent_id":   0,
  "reply_uid":   0,
  "create_time": "2024-08-01 09:30:00",
  "replies": [
    {
      "id":          5002,
      "user_id":     1001,
      "nickname":    "小明同学",
      "content":     "谢谢！",
      "parent_id":   5001,
      "reply_uid":   1002,
      "create_time": "2024-08-01 09:35:00"
    }
  ]
}
```

---

### 7.5 发布帖子

**POST** `/community/createPost`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| title | string | ❌ | 标题（≤ 128 字符） |
| content | string | ✅ | 正文（≤ 2000 字符） |
| images | array | ❌ | 图片 URL 数组（≤ 9 张） |
| post_type | int | ❌ | 类型：1 普通动态，2 问答，3 经验分享（默认 1） |
| topic_id | int | ❌ | 话题 ID |

**返回数据**

```json
{ "code": 0, "message": "发布成功", "data": { "post_id": 1001 } }
```

---

### 7.6 发布评论

**POST** `/community/createComment`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| post_id | int | ✅ | 帖子 ID |
| content | string | ✅ | 评论内容（≤ 500 字符） |
| parent_id | int | ❌ | 父评论 ID（回复时传入） |
| reply_uid | int | ❌ | 被回复的用户 ID |

**返回数据**

```json
{ "code": 0, "message": "评论成功", "data": { "comment_id": 5001 } }
```

---

### 7.7 点赞 / 取消点赞

**POST** `/community/like`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| target_type | string | ✅ | `post`（帖子）或 `comment`（评论） |
| target_id | int | ✅ | 帖子 / 评论 ID |
| action | string | ✅ | `like`（点赞）或 `unlike`（取消） |

**返回数据**

```json
{ "code": 0, "message": "点赞成功", "data": { "like_count": 43 } }
```

---

### 7.8 学习打卡

**POST** `/community/checkIn`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| subject | string | ✅ | 打卡科目（≤ 64 字符） |
| content | string | ❌ | 打卡内容 / 感悟（≤ 500 字符） |
| images | array | ❌ | 打卡图片 URL 数组（≤ 4 张） |
| study_minutes | int | ✅ | 本次学习时长（分钟，> 0） |
| plan_id | int | ❌ | 关联学习计划 ID |

**返回数据**

```json
{
  "code": 0,
  "message": "打卡成功",
  "data": {
    "check_in_id":      201,
    "consecutive_days": 7,
    "today_study_min":  90
  }
}
```

---

### 7.9 打卡记录列表

**GET** `/community/checkInList`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| user_id | int | ❌ | 指定用户 ID（不传则查当前用户） |
| date_start | string | ❌ | 开始日期 `YYYY-MM-DD` |
| date_end | string | ❌ | 结束日期 `YYYY-MM-DD` |
| page | int | ❌ | 页码 |
| limit | int | ❌ | 每页条数 |

**返回数据（list 元素）**

```json
{
  "id":              201,
  "check_date":      "2024-08-01",
  "subject":         "数学",
  "content":         "今天刷了 30 道函数题...",
  "images":          [],
  "study_minutes":   90,
  "consecutive_days":7,
  "like_count":      5,
  "comment_count":   2
}
```

---

### 7.10 创建学习计划

**POST** `/community/createPlan`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| title | string | ✅ | 计划标题（≤ 128 字符） |
| description | string | ❌ | 计划描述 |
| subject | string | ✅ | 科目（≤ 64 字符） |
| start_date | string | ✅ | 开始日期 `YYYY-MM-DD` |
| end_date | string | ✅ | 结束日期 `YYYY-MM-DD` |
| daily_minutes | int | ✅ | 每日计划学习分钟数（> 0） |
| target | string | ❌ | 目标描述（≤ 255 字符） |
| is_public | int | ❌ | 是否公开：1 公开，0 私密（默认 1） |

**返回数据**

```json
{ "code": 0, "message": "计划创建成功", "data": { "plan_id": 10 } }
```

---

### 7.11 学习计划列表

**GET** `/community/plans`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| user_id | int | ❌ | 指定用户（不传则查当前用户） |
| status | int | ❌ | 状态：1 进行中，2 已完成，3 已放弃 |
| page | int | ❌ | 页码 |
| limit | int | ❌ | 每页条数 |

**返回数据（list 元素）**

```json
{
  "id":           10,
  "title":        "暑假数学强化计划",
  "subject":      "数学",
  "start_date":   "2024-07-01",
  "end_date":     "2024-08-31",
  "daily_minutes":120,
  "target":       "提高函数板块成绩至 90 分",
  "status":       1,
  "progress":     45,
  "is_public":    1
}
```

---

## 8 自习模块

### 8.1 自习室列表

**GET** `/study/roomList`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| room_type | int | ❌ | 类型：1 公共，2 专注，3 陪伴（不传查全部） |
| is_free | int | ❌ | 0 收费，1 免费 |

**返回数据（list 元素）**

```json
{
  "id":          1,
  "name":        "公共自习室",
  "description": "免费开放，适合日常自习",
  "cover":       "https://cdn.example.com/room/1.jpg",
  "room_type":   1,
  "capacity":    500,
  "online_count":38,
  "is_free":     1,
  "price":       "0.00",
  "status":      1,
  "tutor_id":    0,
  "tutor_name":  ""
}
```

---

### 8.2 进入自习室

**POST** `/study/enter`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| room_id | int | ❌ | 自习室 ID（0 = 独立专注计时） |
| study_type | int | ✅ | 自习类型（见附录） |
| subject | string | ❌ | 学习科目（≤ 64 字符） |
| target_min | int | ❌ | 目标时长（分钟，> 0） |
| order_id | int | ❌ | 关联订单 ID（陪伴自习传入） |

**返回数据**

```json
{
  "code": 0,
  "message": "进入自习室成功",
  "data": {
    "record_id":  601,
    "start_time": "2024-08-01 09:00:00",
    "target_min": 90
  }
}
```

---

### 8.3 结束自习

**POST** `/study/finish`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| record_id | int | ✅ | 自习记录 ID |
| remark | string | ❌ | 备注 / 感想（≤ 255 字符） |

**返回数据**

```json
{
  "code": 0,
  "message": "自习结束",
  "data": {
    "record_id":    601,
    "duration_min": 87,
    "target_min":   90,
    "is_completed": 0,
    "today_total_min": 210
  }
}
```

---

### 8.4 自习记录列表

**GET** `/study/records`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| study_type | int | ❌ | 自习类型过滤 |
| date_start | string | ❌ | 开始日期 `YYYY-MM-DD` |
| date_end | string | ❌ | 结束日期 `YYYY-MM-DD` |
| page | int | ❌ | 页码 |
| limit | int | ❌ | 每页条数 |

**返回数据（list 元素）**

```json
{
  "id":           601,
  "room_id":      1,
  "room_name":    "公共自习室",
  "study_type":   2,
  "subject":      "英语",
  "start_time":   "2024-08-01 09:00:00",
  "end_time":     "2024-08-01 10:27:00",
  "duration_min": 87,
  "target_min":   90,
  "is_completed": 0,
  "remark":       "今天有点走神..."
}
```

---

## 9 家长监护模块

### 9.1 生成绑定邀请码（学生端）

**GET** `/guardian/genBindCode`

**返回数据**

```json
{
  "code": 0,
  "message": "操作成功",
  "data": {
    "bind_code":  "ABC123DE",
    "expires_at": "2024-08-01 12:00:00"
  }
}
```

---

### 9.2 扫码绑定学生（家长端）

**POST** `/guardian/bind`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| bind_code | string | ✅ | 学生生成的 8 位绑定码 |

**返回数据**

```json
{
  "code": 0,
  "message": "绑定申请已发送，等待学生确认",
  "data": {
    "bind_id":    15,
    "student_id": 1001,
    "bind_status":0
  }
}
```

---

### 9.3 学生确认绑定

**POST** `/guardian/confirmBind`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| bind_id | int | ✅ | 绑定记录 ID |
| action | string | ✅ | `confirm`（同意）或 `reject`（拒绝） |

**返回数据**

```json
{ "code": 0, "message": "绑定成功", "data": null }
```

---

### 9.4 我的监护/被监护列表

**GET** `/guardian/list`

> 学生调用返回家长列表；家长调用返回被监护学生列表。

**返回数据（list 元素）**

```json
{
  "id":              15,
  "bind_status":     1,
  "bind_time":       "2024-08-01 10:00:00",
  "can_view_report": 1,
  "can_set_limit":   1,
  "can_view_order":  1,
  "target": {
    "id":       1001,
    "nickname": "小明同学",
    "avatar":   "https://cdn.example.com/avatar/1001.jpg",
    "school":   "北京市第一中学",
    "grade":    "初三"
  }
}
```

---

### 9.5 解除绑定

**POST** `/guardian/unbind`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| bind_id | int | ✅ | 绑定记录 ID |

**返回数据**

```json
{ "code": 0, "message": "已解除绑定", "data": null }
```

---

### 9.6 修改监护权限

**POST** `/guardian/updatePermission`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| bind_id | int | ✅ | 绑定记录 ID |
| can_view_report | int | ❌ | 是否可查看学习报告：0/1 |
| can_set_limit | int | ❌ | 是否可设置使用限制：0/1 |
| can_view_order | int | ❌ | 是否可查看订单：0/1 |

**返回数据**

```json
{ "code": 0, "message": "权限已更新", "data": null }
```

---

### 9.7 查看子女学习日报（家长端）

**GET** `/guardian/report`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| student_id | int | ✅ | 子女用户 ID |
| date | string | ❌ | 日期 `YYYY-MM-DD`，默认昨日 |

**返回数据**

```json
{
  "code": 0,
  "message": "操作成功",
  "data": {
    "report_date":    "2024-08-01",
    "student_id":     1001,
    "student_name":   "小明同学",
    "study_minutes":  210,
    "check_in_count": 1,
    "order_count":    1,
    "online_minutes": 87,
    "post_count":     2,
    "summary": {
      "subjects":      ["数学", "英语"],
      "check_in_streak":7
    }
  }
}
```

---

### 9.8 查看子女学习日报列表（家长端）

**GET** `/guardian/reportList`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| student_id | int | ✅ | 子女用户 ID |
| date_start | string | ❌ | 开始日期 |
| date_end | string | ❌ | 结束日期 |
| page | int | ❌ | 页码 |
| limit | int | ❌ | 每页条数 |

---

## 10 数据统计模块（管理端）

### 10.1 总览面板

**GET** `/stats/dashboard`

**返回数据**

```json
{
  "code": 0,
  "message": "操作成功",
  "data": {
    "user": {
      "total": 12540,
      "today": 38,
      "month": 860
    },
    "tutor": {
      "total":         318,
      "active":        205,
      "pending_audit": 12
    },
    "order": {
      "total":          9820,
      "today":          43,
      "pending_refund": 3
    },
    "revenue": {
      "total": 589200.00,
      "today": 2580.00,
      "month": 68400.00
    }
  }
}
```

---

### 10.2 趋势图数据

**GET** `/stats/trend`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| type | string | ✅ | `order`（订单量）或 `revenue`（营收） |
| days | int | ❌ | 天数：7 / 14 / 30 / 90，默认 7 |

**返回数据**

```json
{
  "code": 0,
  "message": "操作成功",
  "data": {
    "type": "order",
    "days": 7,
    "list": [
      { "date": "2024-07-26", "value": 41 },
      { "date": "2024-07-27", "value": 38 },
      { "date": "2024-07-28", "value": 0  },
      { "date": "2024-07-29", "value": 52 },
      { "date": "2024-07-30", "value": 49 },
      { "date": "2024-07-31", "value": 61 },
      { "date": "2024-08-01", "value": 43 }
    ]
  }
}
```

---

### 10.3 陪伴师接单排行榜

**GET** `/stats/tutorRank`

**Query 参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| limit | int | ❌ | 返回条数：5–50，默认 10 |

**返回数据（array）**

```json
{
  "code": 0,
  "message": "操作成功",
  "data": [
    {
      "id":           50,
      "real_name":    "李晓华",
      "avatar":       "https://cdn.example.com/avatar/50.jpg",
      "score":        "4.9",
      "order_count":  128,
      "total_income": "7680.00"
    }
  ]
}
```

---

### 10.4 订单状态统计

**GET** `/order/stats`

**返回数据**

```json
{
  "code": 0,
  "message": "操作成功",
  "data": {
    "total":          9820,
    "pending":        105,
    "confirmed":      42,
    "in_service":     18,
    "completed":      9321,
    "cancelled":      328,
    "pending_refund": 3,
    "refunded":       3,
    "total_amount":   589200.00
  }
}
```

---

## 11 附录：枚举常量速查

### 用户类型 `user_type`

| 值 | 说明 |
|----|------|
| 1 | 学生 |
| 2 | 家长 |

### 性别 `gender`

| 值 | 说明 |
|----|------|
| 0 | 未知 |
| 1 | 男 |
| 2 | 女 |

### 学历 `education`

| 值 | 说明 |
|----|------|
| 1 | 高中 |
| 2 | 大专 |
| 3 | 本科 |
| 4 | 硕士 |
| 5 | 博士 |

### 服务类型 `service_type`

| 值 | 说明 |
|----|------|
| 1 | 在线陪伴 |
| 2 | 线上自习 |
| 3 | 学习规划 |

### 陪伴师审核状态 `audit_status`

| 值 | 说明 |
|----|------|
| 0 | 待审核 |
| 1 | 审核通过 |
| 2 | 审核拒绝 |

### 订单状态 `order_status`

| 值 | 说明 |
|----|------|
| 0 | 待确认 |
| 1 | 已确认 |
| 2 | 服务中 |
| 3 | 已完成 |
| 4 | 已取消 |
| 5 | 申请退款 |
| 6 | 已退款 |

### 支付状态 `pay_status`

| 值 | 说明 |
|----|------|
| 0 | 待支付 |
| 1 | 已支付 |
| 2 | 已退款 |

### 支付方式 `pay_type`

| 值 | 说明 |
|----|------|
| 0 | 未支付 |
| 1 | 微信支付 |
| 2 | 余额支付 |

### 帖子类型 `post_type`

| 值 | 说明 |
|----|------|
| 1 | 普通动态 |
| 2 | 问答 |
| 3 | 经验分享 |

### 自习类型 `study_type`

| 值 | 说明 |
|----|------|
| 1 | 专注计时 |
| 2 | 自习室 |
| 3 | 陪伴自习 |

### 自习室类型 `room_type`

| 值 | 说明 |
|----|------|
| 1 | 公共自习 |
| 2 | 专注计时 |
| 3 | 陪伴自习 |

### 钱包流水类型 `log_type`

| 值 | 说明 |
|----|------|
| 1 | 充值 |
| 2 | 消费 |
| 3 | 退款 |
| 4 | 佣金结算 |
| 5 | 提现 |

### 通知类型 `notify_type`

| 值 | 说明 |
|----|------|
| 1 | 系统通知 |
| 2 | 订单通知 |
| 3 | 社区互动 |
| 4 | 审核通知 |
| 5 | 家长通知 |

### 证件文件类型 `file_type`

| 值 | 说明 |
|----|------|
| 1 | 身份证正面 |
| 2 | 身份证反面 |
| 3 | 学历证书 |
| 4 | 资格证书 |
| 5 | 其他 |

### 家长绑定状态 `bind_status`

| 值 | 说明 |
|----|------|
| 0 | 待确认 |
| 1 | 已绑定 |
| 2 | 已解绑 |

### 学习计划状态 `plan_status`

| 值 | 说明 |
|----|------|
| 1 | 进行中 |
| 2 | 已完成 |
| 3 | 已放弃 |
