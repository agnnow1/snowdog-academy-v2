<?php

namespace Snowdog\Academy\Controller;

class Index
{
    public function index(): void
    {
        if (isset($_SESSION['login'])) {
            header('Location: /cryptos');
            return;
        }

        require __DIR__ . '/../view/index/index.phtml';
    }
}
