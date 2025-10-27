<?php

namespace app\enums;

/**
 * Enum статусов пользователя
 *
 * Использует PHP 8.1+ Backed Enums для типобезопасности
 * Вместо магических констант используем строгую типизацию
 *
 * Преимущества:
 * - Автокомплит в IDE
 * - Невозможно передать невалидное значение
 * - Удобные методы для получения label и проверки статуса
 *
 * @author Yii2 Books Catalog
 * @since 1.0
 */
enum UserStatus: int
{
    case DELETED = 0;
    case INACTIVE = 9;
    case ACTIVE = 10;

    /**
     * Возвращает человекочитаемое название статуса
     */
    public function label(): string
    {
        return match($this) {
            self::DELETED => 'Удалён',
            self::INACTIVE => 'Неактивен',
            self::ACTIVE => 'Активен',
        };
    }

    /**
     * Проверяет, является ли статус активным
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Проверяет, может ли пользователь войти в систему
     */
    public function canLogin(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Возвращает CSS класс для отображения в UI
     */
    public function cssClass(): string
    {
        return match($this) {
            self::DELETED => 'text-danger',
            self::INACTIVE => 'text-warning',
            self::ACTIVE => 'text-success',
        };
    }

    /**
     * Возвращает все доступные статусы для dropdown
     *
     * @return array<int, string>
     */
    public static function list(): array
    {
        $list = [];
        foreach (self::cases() as $case) {
            $list[$case->value] = $case->label();
        }
        return $list;
    }

    /**
     * Получить enum из значения (безопасно)
     */
    public static function tryFromValue(?int $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }
}