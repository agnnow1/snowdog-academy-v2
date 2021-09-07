<?php

namespace Snowdog\Academy\Controller;

use Snowdog\Academy\Model\Cryptocurrency;
use Snowdog\Academy\Model\CryptocurrencyManager;
use Snowdog\Academy\Model\UserCryptocurrencyManager;
use Snowdog\Academy\Model\UserManager;

class Cryptos
{
    private CryptocurrencyManager $cryptocurrencyManager;
    private UserCryptocurrencyManager $userCryptocurrencyManager;
    private UserManager $userManager;
    private Cryptocurrency $cryptocurrency;

    public function __construct(
        CryptocurrencyManager $cryptocurrencyManager,
        UserCryptocurrencyManager $userCryptocurrencyManager,
        UserManager $userManager
    ) {
        $this->cryptocurrencyManager = $cryptocurrencyManager;
        $this->userCryptocurrencyManager = $userCryptocurrencyManager;
        $this->userManager = $userManager;
    }

    public function index(): void
    {
        require __DIR__ . '/../view/cryptos/index.phtml';
    }

    public function buy(string $id): void
    {
        $user = $this->userManager->getByLogin((string) $_SESSION['login']);
        if (!$user) {
            header('Location: /cryptos');
            return;
        }

        $cryptocurrency = $this->cryptocurrencyManager->getCryptocurrencyById($id);
        if (!$cryptocurrency) {
            header('Location: /cryptos');
            return;
        }

        $this->cryptocurrency = $cryptocurrency;
        require __DIR__ . '/../view/cryptos/buy.phtml';
    }

    public function buyPost(string $id): void
    {
        $user = $this->userManager->getByLogin((string) $_SESSION['login']);
        if (!$user) {
            header('Location: /cryptos');
            return;
        }
        $amount = $_POST['amount'] ?? null;

        $cryptocurrency = $this->cryptocurrencyManager->getCryptocurrencyById($id);
        if (!$cryptocurrency) {
            header('Location: /cryptos');
            return;
        }

        if ($amount && (int) $amount !== 0) {
            $userFunds = $user->getFunds();
            $cryptoCurrencyCost = $cryptocurrency->getPrice() * $amount;

            if ($userFunds >= $cryptoCurrencyCost) {
                $this->userCryptocurrencyManager->addCryptocurrencyToUser($user->getId(), $cryptocurrency, (int) $amount);
                $this->userManager->subtractFundsFromUser($user, $cryptoCurrencyCost);
            }
            else {
                $_SESSION['flash'] = "You don't have enough money!";
            }
        }

        header('Location: /cryptos');
    }

    public function sell(string $id): void
    {
        $user = $this->userManager->getByLogin((string) $_SESSION['login']);
        if (!$user) {
            header('Location: /account');
            return;
        }

        $cryptocurrency = $this->cryptocurrencyManager->getCryptocurrencyById($id);
        if (!$cryptocurrency) {
            header('Location: /account');
            return;
        }

        $this->cryptocurrency = $cryptocurrency;
        require __DIR__ . '/../view/cryptos/sell.phtml';
    }

    public function sellPost(string $id): void
    {
        $user = $this->userManager->getByLogin((string) $_SESSION['login']);
        if (!$user) {
            header('Location: /cryptos');
            return;
        }

        $cryptocurrency = $this->cryptocurrencyManager->getCryptocurrencyById($id);
        if (!$cryptocurrency) {
            header('Location: /cryptos');
            return;
        }

        $amount = $_POST['amount'] ?? null;

        if ($amount && (int) $amount !== 0) {
            try {
                $this->userCryptocurrencyManager->subtractCryptocurrencyFromUser($user->getId(), $cryptocurrency, (int)$amount);
                $_SESSION['flash'] = 'Cryptocurrency sold successfully';
                $this->userManager->addFunds($user, $cryptocurrency->getPrice() * $amount);
            } catch (\Exception $e) {
                $_SESSION['flash'] = $e->getMessage();
            }
        }

        header('Location: /account');
    }

    public function getCryptocurrencies(): array
    {
        return $this->cryptocurrencyManager->getAllCryptocurrencies();
    }
}