<?php

namespace Snowdog\Academy\Menu;

class AddFundsMenu extends AbstractMenu
{
    public function getHref(): string
    {
        return '/account/addFunds';
    }

    public function getLabel(): string
    {
        return 'Add Funds';
    }

    public function isVisible(): bool
    {
        return isset($_SESSION['login']);
    }
}