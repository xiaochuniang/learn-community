-- =====================================================================
-- 中小学生 & 青少年学习社区 — 完整数据库建表 SQL
-- 数据库版本：MySQL 8.0+
-- 字符集：utf8mb4  排序规则：utf8mb4_unicode_ci
-- 规范：主键 id、create_time、update_time、is_delete、合理索引、完整注释
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `learn_community`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `learn_community`;

-- =====================================================================
-- 1. 管理员表 lc_admin
-- =====================================================================
CREATE TABLE `lc_admin` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `username`    VARCHAR(64)     NOT NULL                COMMENT '登录账号',
  `password`    VARCHAR(128)    NOT NULL                COMMENT '登录密码（bcrypt）',
  `real_name`   VARCHAR(32)     NOT NULL DEFAULT ''     COMMENT '真实姓名',
  `avatar`      VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '头像URL',
  `email`       VARCHAR(128)    NOT NULL DEFAULT ''     COMMENT '邮箱',
  `mobile`      VARCHAR(20)     NOT NULL DEFAULT ''     COMMENT '手机号',
  `role_id`     BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '角色ID，关联 lc_role',
  `status`      TINYINT         NOT NULL DEFAULT 1      COMMENT '状态：1启用 0禁用',
  `last_login_ip`  VARCHAR(45)  NOT NULL DEFAULT ''     COMMENT '最后登录IP',
  `last_login_at`  DATETIME     NULL                    COMMENT '最后登录时间',
  `is_delete`   TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

-- =====================================================================
-- 2. 角色表 lc_role（权限管理基础）
-- =====================================================================
CREATE TABLE `lc_role` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name`        VARCHAR(64)     NOT NULL                COMMENT '角色名称',
  `code`        VARCHAR(64)     NOT NULL                COMMENT '角色标识（英文唯一）',
  `description` VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '角色描述',
  `permissions` JSON            NULL                    COMMENT '权限节点JSON数组',
  `status`      TINYINT         NOT NULL DEFAULT 1      COMMENT '状态：1启用 0禁用',
  `is_delete`   TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色表';

-- =====================================================================
-- 3. 用户表 lc_user（学生 / 家长）
-- =====================================================================
CREATE TABLE `lc_user` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `openid`          VARCHAR(128)    NOT NULL DEFAULT ''     COMMENT '微信openid',
  `unionid`         VARCHAR(128)    NOT NULL DEFAULT ''     COMMENT '微信unionid',
  `nickname`        VARCHAR(64)     NOT NULL DEFAULT ''     COMMENT '昵称',
  `avatar`          VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '头像URL',
  `real_name`       VARCHAR(32)     NOT NULL DEFAULT ''     COMMENT '真实姓名',
  `mobile`          VARCHAR(20)     NOT NULL DEFAULT ''     COMMENT '手机号',
  `gender`          TINYINT         NOT NULL DEFAULT 0      COMMENT '性别：0未知 1男 2女',
  `birthday`        DATE            NULL                    COMMENT '生日',
  `school`          VARCHAR(128)    NOT NULL DEFAULT ''     COMMENT '学校名称',
  `grade`           VARCHAR(32)     NOT NULL DEFAULT ''     COMMENT '年级，如"初三"',
  `user_type`       TINYINT         NOT NULL DEFAULT 1      COMMENT '用户类型：1学生 2家长',
  `status`          TINYINT         NOT NULL DEFAULT 1      COMMENT '状态：1正常 0禁用',
  `balance`         DECIMAL(10,2)   NOT NULL DEFAULT 0.00   COMMENT '钱包余额（元）',
  `total_study_min` INT             NOT NULL DEFAULT 0      COMMENT '累计学习分钟数',
  `last_login_at`   DATETIME        NULL                    COMMENT '最后登录时间',
  `last_login_ip`   VARCHAR(45)     NOT NULL DEFAULT ''     COMMENT '最后登录IP',
  `register_source` TINYINT         NOT NULL DEFAULT 1      COMMENT '注册来源：1微信小程序',
  `is_delete`       TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_openid` (`openid`),
  KEY `idx_mobile` (`mobile`),
  KEY `idx_user_type` (`user_type`),
  KEY `idx_status` (`status`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表（学生/家长）';

-- =====================================================================
-- 4. 陪伴师表 lc_tutor
-- =====================================================================
CREATE TABLE `lc_tutor` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id`         BIGINT UNSIGNED NOT NULL                COMMENT '关联用户ID（lc_user）',
  `real_name`       VARCHAR(32)     NOT NULL DEFAULT ''     COMMENT '真实姓名',
  `mobile`          VARCHAR(20)     NOT NULL DEFAULT ''     COMMENT '联系手机号',
  `avatar`          VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '头像URL',
  `gender`          TINYINT         NOT NULL DEFAULT 0      COMMENT '性别：0未知 1男 2女',
  `age`             TINYINT UNSIGNED NOT NULL DEFAULT 0     COMMENT '年龄',
  `education`       TINYINT         NOT NULL DEFAULT 1      COMMENT '学历：1高中 2大专 3本科 4硕士 5博士',
  `school`          VARCHAR(128)    NOT NULL DEFAULT ''     COMMENT '毕业院校',
  `major`           VARCHAR(64)     NOT NULL DEFAULT ''     COMMENT '专业',
  `subjects`        VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '擅长科目，逗号分隔',
  `introduction`    TEXT            NULL                    COMMENT '个人简介',
  `service_price`   DECIMAL(8,2)    NOT NULL DEFAULT 0.00   COMMENT '服务单价（元/小时）',
  `service_type`    TINYINT         NOT NULL DEFAULT 1      COMMENT '服务类型：1在线陪伴 2线上自习 3学习规划',
  `audit_status`    TINYINT         NOT NULL DEFAULT 0      COMMENT '审核状态：0待审核 1通过 2拒绝',
  `audit_remark`    VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '审核备注',
  `audit_at`        DATETIME        NULL                    COMMENT '审核时间',
  `audit_admin_id`  BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '审核管理员ID',
  `status`          TINYINT         NOT NULL DEFAULT 0      COMMENT '上线状态：0下线 1上线',
  `score`           DECIMAL(3,1)    NOT NULL DEFAULT 5.0    COMMENT '综合评分（1-5）',
  `order_count`     INT             NOT NULL DEFAULT 0      COMMENT '累计接单数',
  `total_income`    DECIMAL(10,2)   NOT NULL DEFAULT 0.00   COMMENT '累计收入（元）',
  `commission_rate` DECIMAL(4,2)    NOT NULL DEFAULT 0.80   COMMENT '佣金比例（默认80%归陪伴师）',
  `is_delete`       TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_id` (`user_id`),
  KEY `idx_audit_status` (`audit_status`),
  KEY `idx_status` (`status`),
  KEY `idx_score` (`score`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='陪伴师信息表';

-- =====================================================================
-- 5. 陪伴师资质审核表 lc_tutor_audit
-- =====================================================================
CREATE TABLE `lc_tutor_audit` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `tutor_id`       BIGINT UNSIGNED NOT NULL                COMMENT '陪伴师ID（lc_tutor）',
  `user_id`        BIGINT UNSIGNED NOT NULL                COMMENT '用户ID（lc_user）',
  `audit_type`     TINYINT         NOT NULL DEFAULT 1      COMMENT '审核类型：1入驻申请 2资质变更',
  `submit_data`    JSON            NULL                    COMMENT '申请提交的完整数据快照（JSON）',
  `status`         TINYINT         NOT NULL DEFAULT 0      COMMENT '审核状态：0待审核 1通过 2拒绝',
  `remark`         VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '审核意见',
  `admin_id`       BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '审核操作员ID',
  `audit_at`       DATETIME        NULL                    COMMENT '审核时间',
  `is_delete`      TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '提交时间',
  `update_time`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tutor_id` (`tutor_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='陪伴师资质审核流程表';

-- =====================================================================
-- 6. 证件/资质文件上传表 lc_cert_file
-- =====================================================================
CREATE TABLE `lc_cert_file` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id`     BIGINT UNSIGNED NOT NULL                COMMENT '上传者用户ID',
  `tutor_id`    BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '陪伴师ID，非陪伴师则为0',
  `file_type`   TINYINT         NOT NULL DEFAULT 1      COMMENT '文件类型：1身份证正面 2身份证反面 3学历证书 4资格证书 5其他',
  `file_url`    VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '文件存储URL（OSS）',
  `file_name`   VARCHAR(128)    NOT NULL DEFAULT ''     COMMENT '原始文件名',
  `file_size`   INT             NOT NULL DEFAULT 0      COMMENT '文件大小（字节）',
  `mime_type`   VARCHAR(64)     NOT NULL DEFAULT ''     COMMENT 'MIME类型',
  `ocr_result`  JSON            NULL                    COMMENT 'OCR识别结果（JSON）',
  `status`      TINYINT         NOT NULL DEFAULT 1      COMMENT '状态：1有效 0无效',
  `is_delete`   TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '上传时间',
  `update_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_tutor_id` (`tutor_id`),
  KEY `idx_file_type` (`file_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='证件/资质文件上传表';

-- =====================================================================
-- 7. 社区动态表 lc_post
-- =====================================================================
CREATE TABLE `lc_post` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id`      BIGINT UNSIGNED NOT NULL                COMMENT '发布者用户ID',
  `title`        VARCHAR(128)    NOT NULL DEFAULT ''     COMMENT '标题（可选）',
  `content`      TEXT            NOT NULL                COMMENT '正文内容',
  `images`       JSON            NULL                    COMMENT '图片URL数组（JSON）',
  `post_type`    TINYINT         NOT NULL DEFAULT 1      COMMENT '类型：1普通动态 2问答 3经验分享',
  `topic_id`     BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '话题ID，关联 lc_topic',
  `like_count`   INT             NOT NULL DEFAULT 0      COMMENT '点赞数',
  `comment_count`INT             NOT NULL DEFAULT 0      COMMENT '评论数',
  `view_count`   INT             NOT NULL DEFAULT 0      COMMENT '浏览数',
  `is_top`       TINYINT         NOT NULL DEFAULT 0      COMMENT '是否置顶：0否 1是',
  `is_essence`   TINYINT         NOT NULL DEFAULT 0      COMMENT '是否精华：0否 1是',
  `audit_status` TINYINT         NOT NULL DEFAULT 1      COMMENT '审核状态：0待审 1通过 2拒绝',
  `audit_remark` VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '审核备注',
  `status`       TINYINT         NOT NULL DEFAULT 1      COMMENT '显示状态：1正常 0隐藏',
  `is_delete`    TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发布时间',
  `update_time`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_post_type` (`post_type`),
  KEY `idx_topic_id` (`topic_id`),
  KEY `idx_audit_status` (`audit_status`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='社区动态/帖子表';

-- =====================================================================
-- 8. 话题/分类表 lc_topic
-- =====================================================================
CREATE TABLE `lc_topic` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name`        VARCHAR(64)     NOT NULL                COMMENT '话题名称',
  `icon`        VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '话题图标URL',
  `description` VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '话题描述',
  `post_count`  INT             NOT NULL DEFAULT 0      COMMENT '帖子数量',
  `sort`        INT             NOT NULL DEFAULT 0      COMMENT '排序权重（越大越靠前）',
  `status`      TINYINT         NOT NULL DEFAULT 1      COMMENT '状态：1正常 0隐藏',
  `is_delete`   TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='话题/分类表';

-- =====================================================================
-- 9. 帖子评论表 lc_post_comment
-- =====================================================================
CREATE TABLE `lc_post_comment` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `post_id`     BIGINT UNSIGNED NOT NULL                COMMENT '帖子ID',
  `user_id`     BIGINT UNSIGNED NOT NULL                COMMENT '评论者用户ID',
  `parent_id`   BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '父评论ID，0为顶级评论',
  `reply_uid`   BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '被回复用户ID',
  `content`     VARCHAR(1000)   NOT NULL                COMMENT '评论内容',
  `like_count`  INT             NOT NULL DEFAULT 0      COMMENT '点赞数',
  `status`      TINYINT         NOT NULL DEFAULT 1      COMMENT '状态：1正常 0隐藏',
  `is_delete`   TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '评论时间',
  `update_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_post_id` (`post_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='帖子评论表';

-- =====================================================================
-- 10. 学习打卡表 lc_check_in
-- =====================================================================
CREATE TABLE `lc_check_in` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id`       BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `check_date`    DATE            NOT NULL                COMMENT '打卡日期',
  `subject`       VARCHAR(64)     NOT NULL DEFAULT ''     COMMENT '打卡科目',
  `content`       VARCHAR(500)    NOT NULL DEFAULT ''     COMMENT '打卡内容/感悟',
  `images`        JSON            NULL                    COMMENT '打卡图片URL数组（JSON）',
  `study_minutes` INT             NOT NULL DEFAULT 0      COMMENT '本次学习时长（分钟）',
  `plan_id`       BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '关联学习计划ID，可为0',
  `like_count`    INT             NOT NULL DEFAULT 0      COMMENT '点赞数',
  `comment_count` INT             NOT NULL DEFAULT 0      COMMENT '评论数',
  `consecutive_days` INT          NOT NULL DEFAULT 1      COMMENT '连续打卡天数',
  `status`        TINYINT         NOT NULL DEFAULT 1      COMMENT '状态：1正常 0隐藏',
  `is_delete`     TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '打卡时间',
  `update_time`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_date` (`user_id`, `check_date`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_check_date` (`check_date`),
  KEY `idx_plan_id` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='学习打卡记录表';

-- =====================================================================
-- 11. 学习计划表 lc_study_plan
-- =====================================================================
CREATE TABLE `lc_study_plan` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id`       BIGINT UNSIGNED NOT NULL                COMMENT '制定者用户ID',
  `title`         VARCHAR(128)    NOT NULL                COMMENT '计划标题',
  `description`   TEXT            NULL                    COMMENT '计划描述',
  `subject`       VARCHAR(64)     NOT NULL DEFAULT ''     COMMENT '科目',
  `start_date`    DATE            NOT NULL                COMMENT '计划开始日期',
  `end_date`      DATE            NOT NULL                COMMENT '计划结束日期',
  `daily_minutes` INT             NOT NULL DEFAULT 60     COMMENT '每日计划学习分钟数',
  `target`        VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '目标描述',
  `status`        TINYINT         NOT NULL DEFAULT 1      COMMENT '状态：1进行中 2已完成 3已放弃',
  `progress`      TINYINT         NOT NULL DEFAULT 0      COMMENT '完成进度（0-100）',
  `is_public`     TINYINT         NOT NULL DEFAULT 1      COMMENT '是否公开：1公开 0私密',
  `is_delete`     TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_start_date` (`start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='学习计划表';

-- =====================================================================
-- 12. 订单表 lc_order
-- =====================================================================
CREATE TABLE `lc_order` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `order_no`       VARCHAR(32)     NOT NULL                COMMENT '订单号（唯一，业务生成）',
  `user_id`        BIGINT UNSIGNED NOT NULL                COMMENT '下单用户ID（学生/家长）',
  `tutor_id`       BIGINT UNSIGNED NOT NULL                COMMENT '陪伴师ID',
  `service_type`   TINYINT         NOT NULL DEFAULT 1      COMMENT '服务类型：1在线陪伴 2自习陪伴 3学习规划',
  `service_date`   DATE            NULL                    COMMENT '预约服务日期',
  `service_start`  TIME            NULL                    COMMENT '服务开始时间',
  `service_end`    TIME            NULL                    COMMENT '服务结束时间',
  `service_minutes`INT             NOT NULL DEFAULT 0      COMMENT '服务时长（分钟）',
  `amount`         DECIMAL(10,2)   NOT NULL DEFAULT 0.00   COMMENT '订单金额（元）',
  `pay_amount`     DECIMAL(10,2)   NOT NULL DEFAULT 0.00   COMMENT '实付金额（元）',
  `discount_amount`DECIMAL(10,2)   NOT NULL DEFAULT 0.00   COMMENT '优惠金额（元）',
  `pay_type`       TINYINT         NOT NULL DEFAULT 0      COMMENT '支付方式：0未支付 1微信支付 2余额支付',
  `pay_status`     TINYINT         NOT NULL DEFAULT 0      COMMENT '支付状态：0待支付 1已支付 2已退款',
  `pay_time`       DATETIME        NULL                    COMMENT '支付时间',
  `wx_trade_no`    VARCHAR(64)     NOT NULL DEFAULT ''     COMMENT '微信支付流水号',
  `order_status`   TINYINT         NOT NULL DEFAULT 0      COMMENT '订单状态：0待确认 1已确认 2服务中 3已完成 4已取消 5申请退款 6已退款',
  `remark`         VARCHAR(500)    NOT NULL DEFAULT ''     COMMENT '用户备注',
  `cancel_reason`  VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '取消/退款原因',
  `finish_time`    DATETIME        NULL                    COMMENT '订单完成时间',
  `is_delete`      TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '下单时间',
  `update_time`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_tutor_id` (`tutor_id`),
  KEY `idx_order_status` (`order_status`),
  KEY `idx_pay_status` (`pay_status`),
  KEY `idx_service_date` (`service_date`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单表';

-- =====================================================================
-- 13. 订单评价表 lc_order_review
-- =====================================================================
CREATE TABLE `lc_order_review` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `order_id`    BIGINT UNSIGNED NOT NULL                COMMENT '订单ID',
  `user_id`     BIGINT UNSIGNED NOT NULL                COMMENT '评价用户ID',
  `tutor_id`    BIGINT UNSIGNED NOT NULL                COMMENT '陪伴师ID',
  `score`       TINYINT         NOT NULL DEFAULT 5      COMMENT '评分：1-5',
  `content`     VARCHAR(500)    NOT NULL DEFAULT ''     COMMENT '评价内容',
  `images`      JSON            NULL                    COMMENT '评价图片URL数组',
  `is_anonymous`TINYINT         NOT NULL DEFAULT 0      COMMENT '是否匿名：0否 1是',
  `status`      TINYINT         NOT NULL DEFAULT 1      COMMENT '状态：1正常 0隐藏',
  `is_delete`   TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '评价时间',
  `update_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_id` (`order_id`),
  KEY `idx_tutor_id` (`tutor_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单评价表';

-- =====================================================================
-- 14. 佣金/收益流水表 lc_commission
-- =====================================================================
CREATE TABLE `lc_commission` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `order_id`        BIGINT UNSIGNED NOT NULL                COMMENT '关联订单ID',
  `tutor_id`        BIGINT UNSIGNED NOT NULL                COMMENT '陪伴师ID',
  `order_amount`    DECIMAL(10,2)   NOT NULL DEFAULT 0.00   COMMENT '订单总金额（元）',
  `commission_rate` DECIMAL(4,2)    NOT NULL DEFAULT 0.80   COMMENT '陪伴师佣金比例',
  `tutor_income`    DECIMAL(10,2)   NOT NULL DEFAULT 0.00   COMMENT '陪伴师实得金额（元）',
  `platform_income` DECIMAL(10,2)   NOT NULL DEFAULT 0.00   COMMENT '平台收入（元）',
  `status`          TINYINT         NOT NULL DEFAULT 0      COMMENT '结算状态：0待结算 1已结算 2已冻结',
  `settle_time`     DATETIME        NULL                    COMMENT '结算时间',
  `freeze_reason`   VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '冻结原因',
  `remark`          VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '备注',
  `is_delete`       TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_id` (`order_id`),
  KEY `idx_tutor_id` (`tutor_id`),
  KEY `idx_status` (`status`),
  KEY `idx_settle_time` (`settle_time`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='陪伴师佣金/收益流水表';

-- =====================================================================
-- 15. 提现申请表 lc_withdraw
-- =====================================================================
CREATE TABLE `lc_withdraw` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `tutor_id`     BIGINT UNSIGNED NOT NULL                COMMENT '陪伴师ID',
  `user_id`      BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `amount`       DECIMAL(10,2)   NOT NULL DEFAULT 0.00   COMMENT '申请提现金额（元）',
  `actual_amount`DECIMAL(10,2)   NOT NULL DEFAULT 0.00   COMMENT '实际到账金额（元，扣手续费后）',
  `fee`          DECIMAL(10,2)   NOT NULL DEFAULT 0.00   COMMENT '手续费（元）',
  `withdraw_type`TINYINT         NOT NULL DEFAULT 1      COMMENT '提现方式：1微信零钱 2银行卡',
  `account`      VARCHAR(128)    NOT NULL DEFAULT ''     COMMENT '到账账号/openid',
  `real_name`    VARCHAR(32)     NOT NULL DEFAULT ''     COMMENT '收款真实姓名',
  `status`       TINYINT         NOT NULL DEFAULT 0      COMMENT '状态：0待审核 1处理中 2已完成 3已拒绝',
  `remark`       VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '审核备注',
  `admin_id`     BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '审核管理员ID',
  `finish_time`  DATETIME        NULL                    COMMENT '完成时间',
  `is_delete`    TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '申请时间',
  `update_time`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tutor_id` (`tutor_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='陪伴师提现申请表';

-- =====================================================================
-- 16. 家长监护绑定表 lc_guardian_bind
-- =====================================================================
CREATE TABLE `lc_guardian_bind` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `parent_id`     BIGINT UNSIGNED NOT NULL                COMMENT '家长用户ID（lc_user.user_type=2）',
  `student_id`    BIGINT UNSIGNED NOT NULL                COMMENT '学生用户ID（lc_user.user_type=1）',
  `bind_code`     VARCHAR(16)     NOT NULL DEFAULT ''     COMMENT '绑定邀请码（学生生成）',
  `bind_status`   TINYINT         NOT NULL DEFAULT 0      COMMENT '绑定状态：0待确认 1已绑定 2已解绑',
  `bind_time`     DATETIME        NULL                    COMMENT '绑定成功时间',
  `unbind_time`   DATETIME        NULL                    COMMENT '解绑时间',
  `can_view_report` TINYINT       NOT NULL DEFAULT 1      COMMENT '是否可查看学习报告：1是 0否',
  `can_set_limit`   TINYINT       NOT NULL DEFAULT 1      COMMENT '是否可设置使用限制：1是 0否',
  `can_view_order`  TINYINT       NOT NULL DEFAULT 1      COMMENT '是否可查看订单：1是 0否',
  `remark`        VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '备注',
  `is_delete`     TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_parent_student` (`parent_id`, `student_id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_bind_code` (`bind_code`),
  KEY `idx_bind_status` (`bind_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='家长监护绑定关系表';

-- =====================================================================
-- 17. 家长监护日报表 lc_guardian_report
-- =====================================================================
CREATE TABLE `lc_guardian_report` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `parent_id`       BIGINT UNSIGNED NOT NULL                COMMENT '家长用户ID',
  `student_id`      BIGINT UNSIGNED NOT NULL                COMMENT '学生用户ID',
  `report_date`     DATE            NOT NULL                COMMENT '报告日期',
  `study_minutes`   INT             NOT NULL DEFAULT 0      COMMENT '当日学习分钟数',
  `check_in_count`  INT             NOT NULL DEFAULT 0      COMMENT '当日打卡次数',
  `order_count`     INT             NOT NULL DEFAULT 0      COMMENT '当日下单次数',
  `online_minutes`  INT             NOT NULL DEFAULT 0      COMMENT '当日在线自习分钟数',
  `post_count`      INT             NOT NULL DEFAULT 0      COMMENT '当日发帖数',
  `summary`         JSON            NULL                    COMMENT '摘要数据（JSON）',
  `is_delete`       TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '生成时间',
  `update_time`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_student_date` (`student_id`, `report_date`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_report_date` (`report_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='家长监护每日学习报告表';

-- =====================================================================
-- 18. 自习室表 lc_study_room
-- =====================================================================
CREATE TABLE `lc_study_room` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name`         VARCHAR(64)     NOT NULL                COMMENT '自习室名称',
  `description`  VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '自习室描述',
  `cover`        VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '封面图URL',
  `room_type`    TINYINT         NOT NULL DEFAULT 1      COMMENT '类型：1公共自习 2专注计时 3陪伴自习',
  `capacity`     INT             NOT NULL DEFAULT 100    COMMENT '最大容纳人数',
  `online_count` INT             NOT NULL DEFAULT 0      COMMENT '当前在线人数',
  `bg_music`     VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '背景音乐URL',
  `tutor_id`     BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '关联陪伴师ID（陪伴自习室）',
  `is_free`      TINYINT         NOT NULL DEFAULT 1      COMMENT '是否免费：1免费 0收费',
  `price`        DECIMAL(8,2)    NOT NULL DEFAULT 0.00   COMMENT '收费价格（元/小时）',
  `status`       TINYINT         NOT NULL DEFAULT 1      COMMENT '状态：1开放 0关闭',
  `is_delete`    TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_room_type` (`room_type`),
  KEY `idx_tutor_id` (`tutor_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='自习室配置表';

-- =====================================================================
-- 19. 自习记录表 lc_study_record
-- =====================================================================
CREATE TABLE `lc_study_record` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id`       BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `room_id`       BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '自习室ID，0为独立专注计时',
  `order_id`      BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '关联订单ID，免费则为0',
  `start_time`    DATETIME        NOT NULL                COMMENT '开始自习时间',
  `end_time`      DATETIME        NULL                    COMMENT '结束自习时间',
  `duration_min`  INT             NOT NULL DEFAULT 0      COMMENT '实际自习时长（分钟）',
  `target_min`    INT             NOT NULL DEFAULT 0      COMMENT '目标自习时长（分钟）',
  `study_type`    TINYINT         NOT NULL DEFAULT 1      COMMENT '类型：1专注计时 2自习室 3陪伴自习',
  `subject`       VARCHAR(64)     NOT NULL DEFAULT ''     COMMENT '学习科目',
  `is_completed`  TINYINT         NOT NULL DEFAULT 0      COMMENT '是否完成目标：0否 1是',
  `remark`        VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '备注/感想',
  `is_delete`     TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_room_id` (`room_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_start_time` (`start_time`),
  KEY `idx_study_type` (`study_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户自习/专注计时记录表';

-- =====================================================================
-- 20. 风控违规记录表 lc_risk_record
-- =====================================================================
CREATE TABLE `lc_risk_record` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id`       BIGINT UNSIGNED NOT NULL                COMMENT '违规用户ID',
  `target_type`   TINYINT         NOT NULL DEFAULT 1      COMMENT '内容类型：1帖子 2评论 3打卡 4用户信息 5陪伴师信息',
  `target_id`     BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '违规内容ID',
  `risk_type`     TINYINT         NOT NULL DEFAULT 1      COMMENT '风险类型：1违禁词 2违规图片 3骚扰 4广告 5其他',
  `risk_content`  TEXT            NULL                    COMMENT '违规原始内容',
  `risk_keywords` VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '命中风险关键词',
  `source`        TINYINT         NOT NULL DEFAULT 1      COMMENT '来源：1系统自动识别 2用户举报 3人工审核',
  `report_user_id`BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '举报用户ID（人工举报时）',
  `status`        TINYINT         NOT NULL DEFAULT 0      COMMENT '处理状态：0待处理 1已处理 2误报',
  `handle_type`   TINYINT         NOT NULL DEFAULT 0      COMMENT '处理方式：0未处理 1警告 2删除内容 3封禁账号 4封禁账号+删内容',
  `handle_remark` VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '处理备注',
  `admin_id`      BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '处理管理员ID',
  `handle_time`   DATETIME        NULL                    COMMENT '处理时间',
  `is_delete`     TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录时间',
  `update_time`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_target_type_id` (`target_type`, `target_id`),
  KEY `idx_risk_type` (`risk_type`),
  KEY `idx_status` (`status`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='风控违规记录表';

-- =====================================================================
-- 21. 用户封禁记录表 lc_user_ban
-- =====================================================================
CREATE TABLE `lc_user_ban` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id`     BIGINT UNSIGNED NOT NULL                COMMENT '被封禁用户ID',
  `ban_type`    TINYINT         NOT NULL DEFAULT 1      COMMENT '封禁类型：1禁止发言 2禁止登录 3永久封禁',
  `reason`      VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '封禁原因',
  `start_time`  DATETIME        NOT NULL                COMMENT '封禁开始时间',
  `end_time`    DATETIME        NULL                    COMMENT '封禁结束时间（NULL为永久）',
  `admin_id`    BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '操作管理员ID',
  `risk_id`     BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '关联风控记录ID',
  `status`      TINYINT         NOT NULL DEFAULT 1      COMMENT '状态：1生效中 0已解封',
  `is_delete`   TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_end_time` (`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户封禁记录表';

-- =====================================================================
-- 22. 系统配置表 lc_system_config
-- =====================================================================
CREATE TABLE `lc_system_config` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `config_key`  VARCHAR(128)    NOT NULL                COMMENT '配置键（唯一英文标识）',
  `config_value`TEXT            NOT NULL                COMMENT '配置值（支持JSON字符串）',
  `config_type` TINYINT         NOT NULL DEFAULT 1      COMMENT '值类型：1字符串 2数字 3布尔 4JSON',
  `group`       VARCHAR(64)     NOT NULL DEFAULT 'base' COMMENT '分组（base/payment/notify/risk/etc）',
  `label`       VARCHAR(64)     NOT NULL DEFAULT ''     COMMENT '配置显示名称',
  `description` VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '配置说明',
  `is_public`   TINYINT         NOT NULL DEFAULT 0      COMMENT '是否向前端公开：0否 1是',
  `sort`        INT             NOT NULL DEFAULT 0      COMMENT '排序权重',
  `is_delete`   TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_config_key` (`config_key`),
  KEY `idx_group` (`group`),
  KEY `idx_is_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统全局配置表';

-- =====================================================================
-- 23. 钱包流水表 lc_wallet_log
-- =====================================================================
CREATE TABLE `lc_wallet_log` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id`      BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `amount`       DECIMAL(10,2)   NOT NULL                COMMENT '变动金额（正数入账，负数出账）',
  `before_balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00   COMMENT '变动前余额',
  `after_balance`  DECIMAL(10,2) NOT NULL DEFAULT 0.00   COMMENT '变动后余额',
  `log_type`     TINYINT         NOT NULL DEFAULT 1      COMMENT '流水类型：1充值 2消费 3退款 4佣金结算 5提现',
  `ref_id`       BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '关联业务ID（订单ID/提现ID等）',
  `ref_type`     VARCHAR(32)     NOT NULL DEFAULT ''     COMMENT '关联业务类型（order/withdraw/commission）',
  `remark`       VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '流水备注',
  `is_delete`    TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '流水时间',
  `update_time`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_log_type` (`log_type`),
  KEY `idx_ref_id_type` (`ref_id`, `ref_type`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户钱包流水表';

-- =====================================================================
-- 24. 消息通知表 lc_notification
-- =====================================================================
CREATE TABLE `lc_notification` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id`      BIGINT UNSIGNED NOT NULL                COMMENT '接收用户ID',
  `title`        VARCHAR(128)    NOT NULL DEFAULT ''     COMMENT '通知标题',
  `content`      VARCHAR(1000)   NOT NULL                COMMENT '通知内容',
  `notify_type`  TINYINT         NOT NULL DEFAULT 1      COMMENT '类型：1系统通知 2订单通知 3社区互动 4审核通知 5家长通知',
  `ref_id`       BIGINT UNSIGNED NOT NULL DEFAULT 0      COMMENT '关联业务ID',
  `ref_type`     VARCHAR(32)     NOT NULL DEFAULT ''     COMMENT '关联业务类型',
  `is_read`      TINYINT         NOT NULL DEFAULT 0      COMMENT '是否已读：0未读 1已读',
  `read_time`    DATETIME        NULL                    COMMENT '阅读时间',
  `is_delete`    TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除：0正常 1已删除',
  `create_time`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id_read` (`user_id`, `is_read`),
  KEY `idx_notify_type` (`notify_type`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='消息通知表';

-- =====================================================================
-- 初始化基础数据
-- =====================================================================

-- 初始化超级管理员角色
INSERT INTO `lc_role` (`name`, `code`, `description`, `permissions`, `status`) VALUES
('超级管理员', 'super_admin', '拥有所有权限', '["*"]', 1),
('运营管理员', 'operator',    '日常运营管理权限', '["user","post","order","check_in","study_plan"]', 1),
('审核专员',   'auditor',     '负责内容与资质审核', '["post_audit","tutor_audit","risk"]', 1);

-- 初始化默认管理员账号（密码：Admin@123，实际使用请修改）
-- password 字段存储 bcrypt hash，此处为占位符，部署时需通过程序生成
INSERT INTO `lc_admin` (`username`, `password`, `real_name`, `role_id`, `status`) VALUES
('admin', '$2y$10$placeholder_replace_with_bcrypt_hash', '系统管理员', 1, 1);

-- 初始化系统配置
INSERT INTO `lc_system_config` (`config_key`, `config_value`, `config_type`, `group`, `label`, `description`, `is_public`) VALUES
('site_name',          '中小学生学习社区',        1, 'base',    '站点名称',         '网站/小程序名称',               1),
('site_logo',          '',                        1, 'base',    '站点Logo URL',     '首页展示Logo',                  1),
('commission_rate',    '0.80',                    2, 'payment', '陪伴师默认佣金比例','平台抽成比例，剩余归陪伴师',      0),
('withdraw_min',       '10.00',                   2, 'payment', '最低提现金额',     '单次最低可提现金额（元）',         0),
('withdraw_fee_rate',  '0.006',                   2, 'payment', '提现手续费率',     '微信零钱提现手续费比例',           0),
('risk_keywords',      '[]',                      4, 'risk',    '风控关键词列表',   'JSON数组，自动审核命中拦截',       0),
('register_open',      '1',                       3, 'base',    '开放注册',         '是否允许新用户注册',               1),
('tutor_apply_open',   '1',                       3, 'base',    '开放陪伴师申请',   '是否允许新陪伴师入驻',             1),
('study_room_bg_music','',                        1, 'base',    '自习室默认背景音乐','默认背景音乐URL',                 1),
('wx_appid',           '',                        1, 'payment', '微信小程序AppID',  '微信开放平台小程序AppID',          0),
('wx_mch_id',          '',                        1, 'payment', '微信商户号',       '微信支付商户号',                   0);

-- 初始化默认话题
INSERT INTO `lc_topic` (`name`, `description`, `sort`, `status`) VALUES
('学习分享',  '分享学习方法与心得',   10, 1),
('打卡记录',  '今日学习打卡',         9,  1),
('问答互助',  '学科难题求解答',        8,  1),
('备考经验',  '考试备考经验交流',     7,  1),
('学习计划',  '分享你的学习计划',     6,  1),
('陪伴师推荐','推荐优质陪伴师',       5,  1);

-- 初始化默认自习室
INSERT INTO `lc_study_room` (`name`, `description`, `room_type`, `capacity`, `is_free`, `status`) VALUES
('公共自习室',   '免费开放，适合日常自习',         1, 500, 1, 1),
('专注计时间',   '番茄钟专注计时，提升学习效率',   2, 999, 1, 1),
('陪伴自习室',   '由认证陪伴师驻守，实时陪伴督促', 3, 50,  0, 1);

-- =====================================================================
-- 25. 虚拟地图点位表 lc_map_point
-- =====================================================================
CREATE TABLE `lc_map_point` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `key`          VARCHAR(64)     NOT NULL                COMMENT '点位唯一标识（英文，如 library）',
  `name`         VARCHAR(64)     NOT NULL                COMMENT '点位名称',
  `icon`         VARCHAR(32)     NOT NULL DEFAULT '📍'  COMMENT '点位图标（Emoji）',
  `description`  VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '点位描述',
  `grid_x`       INT             NOT NULL DEFAULT 0      COMMENT '等距网格X坐标',
  `grid_y`       INT             NOT NULL DEFAULT 0      COMMENT '等距网格Y坐标',
  `build_height` INT             NOT NULL DEFAULT 48     COMMENT '建筑等距高度（像素）',
  `color_top`    VARCHAR(16)     NOT NULL DEFAULT '#87CEEB' COMMENT '建筑顶面颜色',
  `color_left`   VARCHAR(16)     NOT NULL DEFAULT '#5BA3D0' COMMENT '建筑左侧面颜色',
  `color_right`  VARCHAR(16)     NOT NULL DEFAULT '#3A7DB8' COMMENT '建筑右侧面颜色',
  `target_path`  VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '点击进入的小程序页面路径',
  `is_home`      TINYINT         NOT NULL DEFAULT 0      COMMENT '是否为角色初始位置：1是',
  `sort`         INT             NOT NULL DEFAULT 0      COMMENT '排序权重（越大越靠前）',
  `status`       TINYINT         NOT NULL DEFAULT 1      COMMENT '状态：1启用 0禁用',
  `is_delete`    TINYINT         NOT NULL DEFAULT 0      COMMENT '软删除',
  `create_time`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_key` (`key`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='虚拟地图点位表';

-- =====================================================================
-- 26. 虚拟地图人物配置表 lc_map_character（单行配置）
-- =====================================================================
CREATE TABLE `lc_map_character` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name`         VARCHAR(32)     NOT NULL DEFAULT '小明' COMMENT '人物昵称',
  `body_color`   VARCHAR(16)     NOT NULL DEFAULT '#FF9800' COMMENT '衣服颜色',
  `skin_color`   VARCHAR(16)     NOT NULL DEFAULT '#FFCC80' COMMENT '皮肤颜色',
  `hair_color`   VARCHAR(16)     NOT NULL DEFAULT '#5D4037' COMMENT '头发颜色',
  `walk_speed`   DECIMAL(4,2)    NOT NULL DEFAULT 2.00   COMMENT '行走速度（网格/秒）',
  `home_grid_x`  INT             NOT NULL DEFAULT 2      COMMENT '初始网格X',
  `home_grid_y`  INT             NOT NULL DEFAULT 8      COMMENT '初始网格Y',
  `create_time`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='地图卡通人物配置表（单行）';

-- 初始化默认地图点位
INSERT INTO `lc_map_point`
  (`key`,`name`,`icon`,`description`,`grid_x`,`grid_y`,`build_height`,`color_top`,`color_left`,`color_right`,`target_path`,`is_home`,`sort`,`status`)
VALUES
  ('home',       '自己家', '🏠','温暖的出发地',                      2, 8, 52, '#FFE066','#E8C84A','#C8A825','',                  1, 100, 1),
  ('library',    '图书馆', '📚','知识的殿堂，借阅海量书目',            2, 2, 72, '#87CEEB','#5BA3D0','#3A7DB8','/pages/map/library',  0,  90, 1),
  ('study_room', '自习室', '✏️','专注学习，番茄钟陪伴',               8, 2, 64, '#B39DDB','#7E57C2','#512DA8','/pages/study/room',    0,  80, 1),
  ('creative',   '文创店', '🎨','文创周边，激发创意灵感',              8, 7, 56, '#FFAB76','#E87B3A','#C65A1A','/pages/map/creative',  0,  70, 1),
  ('stationery', '文具店', '🖊️','学习文具一站齐，满足所有需求',       5, 9, 52, '#A5D6A7','#66BB6A','#388E3C','/pages/map/stationery',0,  60, 1),
  ('bookstore',  '书店',   '📖','精选好书，发现阅读乐趣',              1, 5, 60, '#EF9A9A','#E57373','#C62828','/pages/map/bookstore',  0,  50, 1),
  ('club',       '社团',   '🎭','加入社团，结交志同道合的朋友',        6, 4, 60, '#80DEEA','#4DD0E1','#0097A7','/pages/map/club',       0,  40, 1);

-- 初始化默认人物配置
INSERT INTO `lc_map_character` (`name`,`body_color`,`skin_color`,`hair_color`,`walk_speed`,`home_grid_x`,`home_grid_y`)
VALUES ('小明','#FF9800','#FFCC80','#5D4037', 2.00, 2, 8);
