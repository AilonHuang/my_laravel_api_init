<?php

namespace App\Models\Enum;

enum UserEnum: int
{
    // 状态类别
    case INVALID = -1; //已删除
    case NORMAL = 0; //正常
    case FREEZE = 1; //冻结

    public static function getStatusName($status): string
    {
        return match ($status) {
            self::INVALID => '已删除',
            self::FREEZE => '冻结',
            default => '正常',
        };
    }
}
