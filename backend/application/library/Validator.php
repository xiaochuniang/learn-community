<?php
/**
 * 链式参数校验器
 *
 * 用法：
 *   $v = new Validator($this->all());
 *   $v->required('name', '姓名不能为空')
 *     ->maxLen('name', 32)
 *     ->mobile('phone');
 *   if ($v->fails()) {
 *       $this->fail($v->firstError());
 *   }
 */
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $message = ''): static
    {
        $val = $this->data[$field] ?? null;
        if ($val === null || $val === '' || (is_array($val) && count($val) === 0)) {
            $this->errors[$field] = $message ?: "{$field} 不能为空";
        }
        return $this;
    }

    public function maxLen(string $field, int $max, string $message = ''): static
    {
        $val = $this->data[$field] ?? '';
        if (is_string($val) && mb_strlen($val) > $max) {
            $this->errors[$field] = $message ?: "{$field} 最长 {$max} 个字符";
        }
        return $this;
    }

    public function minLen(string $field, int $min, string $message = ''): static
    {
        $val = $this->data[$field] ?? '';
        if (is_string($val) && mb_strlen($val) < $min) {
            $this->errors[$field] = $message ?: "{$field} 最短 {$min} 个字符";
        }
        return $this;
    }

    public function mobile(string $field, string $message = ''): static
    {
        $val = (string)($this->data[$field] ?? '');
        if ($val !== '' && !preg_match('/^1[3-9]\d{9}$/', $val)) {
            $this->errors[$field] = $message ?: "请输入有效的11位手机号";
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $message = ''): static
    {
        $val = $this->data[$field] ?? null;
        if ($val !== null && $val !== '' && !in_array($val, $allowed, false)) {
            $this->errors[$field] = $message ?: "{$field} 值不在允许范围内";
        }
        return $this;
    }

    public function integer(string $field, string $message = ''): static
    {
        $val = $this->data[$field] ?? null;
        if ($val !== null && $val !== '' && filter_var($val, FILTER_VALIDATE_INT) === false) {
            $this->errors[$field] = $message ?: "{$field} 必须是整数";
        }
        return $this;
    }

    public function min(string $field, float $minVal, string $message = ''): static
    {
        $val = $this->data[$field] ?? null;
        if ($val !== null && $val !== '' && (float)$val < $minVal) {
            $this->errors[$field] = $message ?: "{$field} 不能小于 {$minVal}";
        }
        return $this;
    }

    public function max(string $field, float $maxVal, string $message = ''): static
    {
        $val = $this->data[$field] ?? null;
        if ($val !== null && $val !== '' && (float)$val > $maxVal) {
            $this->errors[$field] = $message ?: "{$field} 不能大于 {$maxVal}";
        }
        return $this;
    }

    public function date(string $field, string $message = ''): static
    {
        $val = (string)($this->data[$field] ?? '');
        if ($val !== '' && strtotime($val) === false) {
            $this->errors[$field] = $message ?: "{$field} 日期格式不正确";
        }
        return $this;
    }

    public function fails(): bool
    {
        return count($this->errors) > 0;
    }

    public function firstError(): string
    {
        return reset($this->errors) ?: '参数错误';
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
