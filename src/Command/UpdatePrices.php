<?php

namespace Snowdog\Academy\Command;

use Snowdog\Academy\Model\CryptocurrencyManager;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatePrices
{
    const API_URL = 'https://api.coincap.io/v2/assets?ids=%s';
    const API_ERROR_TEXT = 'Too many requests, please try again later.';
    private CryptocurrencyManager $cryptocurrencyManager;

    public function __construct(CryptocurrencyManager $cryptocurrencyManager)
    {
        $this->cryptocurrencyManager = $cryptocurrencyManager;
    }

    public function __invoke(OutputInterface $output)
    {
        $cryptoCurrenciesIds = $this->cryptocurrencyManager->getAllCryptocurrenciesIds();

        try {
            $cryptoCurrenciesApiData = $this->getApiData($cryptoCurrenciesIds);

            foreach ($cryptoCurrenciesApiData['data'] as $apiCryptoCurrency) {
                $this->cryptocurrencyManager->updatePrice($apiCryptoCurrency['id'], (float) $apiCryptoCurrency['priceUsd']);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @throws \Exception
     */
    private function getApiData(string $cryptoCurrenciesIds): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => sprintf(self::API_URL, $cryptoCurrenciesIds),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        curl_close($curl);

        if ($response === self::API_ERROR_TEXT) {
            return $this->getApiData($cryptoCurrenciesIds);
        }

        if ($responseCode !== 200) {
            throw new \Exception("An api error occurred, please try again later.\n");
        }

        return json_decode($response, true);
    }
}
