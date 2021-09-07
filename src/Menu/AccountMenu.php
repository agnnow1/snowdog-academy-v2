<?php

namespace Snowdog\Academy\Menu;

class AccountMenu extends AbstractMenu
{
    public function getHref(): string
    {
        return '/account';
    }

    public function getLabel(): string
    {
        return 'Account';
    }

    public function isVisible(): bool
    {
        return isset($_SESSION['login']);
    }
}
