<?php
/**
 * Model 基类
 * 封装分页结果标准格式
 */
abstract class BaseModel
{
    protected Db $db;
    protected string $table = '';

    public function __construct()
    {
        $this->db = Db::getInstance();
    }

    /**
     * 构造统一分页返回结构
     *
     * @param int   $page  当前页码
     * @param int   $limit 每页条数
     * @param array $list  当页数据
     * @param int   $total 总条数
     */
    protected function buildPageResult(int $page, int $limit, array $list, int $total): array
    {
        return [
            'list'  => $list,
            'total' => $total,
            'page'  => $page,
            'limit' => $limit,
            'pages' => $limit > 0 ? (int)ceil($total / $limit) : 0,
        ];
    }
}
