<?php

/**
 * Poloniex API.
 *
 * @author Haku Kimura
 * @version 1.0
 * @link https://github.com/HakuKimura/poloniex-api
 */
class PoloniexAPI
{
    /**
     * @var string
     */
    private $_apiKey;
    /**
     * @var string
     */
    private $_secretKey;
    /**
     * @var string
     */
    private $_publicUrl = 'https://poloniex.com/public';
    /**
     * @var string
     */
    private $_tradingUrl = 'https://poloniex.com/tradingApi';
    /**
     * @var string
     */
    private $_userAgent;

    /**
     * Constructor.
     */
    public function __construct($apiKey, $secretKey)
    {
        $this->_apiKey = $apiKey;
        $this->_secretKey = $secretKey;
        $this->_userAgent = 'Mozilla/5.0 (' . implode('; ', [
            'compatible',
            'Poloniex PHP bot',
            'Windows NT 6.1',
            'Win64',
            'x64',
            'rv:63.0',
            'PHP/' . phpversion()
        ]) . ')';
    }

    /**
     * Returns the ticker for all markets.
     *
     * @param string $pair selected currency pair
     * @return object|null
     */
    public function returnTicker($pair = null)
    {
        $markets = $this->performGetRequest('returnTicker');
        return $this->getPair($markets, $pair);
    }

    /**
     * Returns the 24-hour volume for all markets, plus totals for primary currencies.
     *
     * @param string $pair selected currency pair
     * @return object|null
     */
    public function return24Volume($pair = null)
    {
        $markets = $this->performGetRequest('return24hVolume');
        return $this->getPair($markets, $pair);
    }

    /**
     * Returns the order book for a given market, as well as a sequence number for use with
     * the Push API and an indicator specifying whether the market is frozen.
     *
     * @param string $pair selected currency pair.
     * Set pair to "all" to get the order books of all markets
     * @param integer $depth
     * @return object
     */
    public function returnOrderBook($pair = 'all', $depth = 10)
    {
        return $this->performGetRequest('returnOrderBook', [
            'currencyPair' => $pair,
            'depth' => $depth
        ]);
    }

    /**
     * Returns the past 200 trades for a given market, or up to 50,000 trades between a range
     * specified in UNIX timestamps by the "start" and "end" GET parameters.
     *
     * @param string $pair selected currency pair
     * @param string $start start date in UNIX timestamp format
     * @param string $end end date in UNIX timestamp format
     * @return array
     */
    public function returnCommonTradeHistory($pair, $start = null, $end = null)
    {
        return $this->performGetRequest('returnTradeHistory', [
            'currencyPair' => $pair,
            'start' => $start,
            'end' => $end
        ]);
    }

    /**
     * Returns candlestick chart data.
     *
     * @param string $pair selected currency pair
     * @param string $start start date in UNIX timestamp format
     * @param string $end end date in UNIX timestamp format
     * @param integer $period candlestick period in seconds.
     * Valid values are 300, 900, 1800, 7200, 14400, and 86400
     * @return array
     */
    public function returnChartData($pair, $start, $end, $period = 14400)
    {
        return $this->performGetRequest('returnChartData', [
            'currencyPair' => $pair,
            'start' => $start,
            'end' => $end,
            'period' => $period
        ]);
    }

    /**
     * Returns information about currencies.
     *
     * @param string $currency
     * @return object
     */
    public function returnCurrencies($currency = null)
    {
        $info = $this->performGetRequest('returnCurrencies');
        return $this->getPair($info, $currency);
    }

    /**
     * Returns the list of loan offers and demands for a given currency (lending).
     *
     * @param string $currency
     * @return object
     */
    public function returnLoanOrders($currency)
    {
        return $this->performGetRequest('returnLoanOrders', [
            'currency' => $currency
        ]);
    }

    /**
     * Returns all of your available balances.
     *
     * @return object
     */
    public function returnBalances()
    {
        return $this->performPostRequest([
            'command' => 'returnBalances'
        ]);
    }

    /**
     * Returns all of your balances, including available balance, balance on orders, and
     * the estimated BTC value of your balance.
     *
     * @return object
     */
    public function returnCompleteBalances()
    {
        return $this->performPostRequest([
            'command' => 'returnCompleteBalances'
        ]);
    }

    /**
     * Returns all of your deposit addresses.
     *
     * @return object
     */
    public function returnDepositAddresses()
    {
        return $this->performPostRequest([
            'command' => 'returnDepositAddresses'
        ]);
    }

    /**
     * Returns your deposit and withdrawal history within a range, specified by
     * the "start" and "end" parameters.
     *
     * @param string $start start date in UNIX timestamp format
     * @param string $end end date in UNIX timestamp format
     * @return object
     */
    public function returnDepositsWithdrawals($start, $end)
    {
        return $this->performPostRequest([
            'command' => 'returnDepositsWithdrawals',
            'start' => $start,
            'end' => $end
        ]);
    }

    /**
     * Returns your open orders for a given market or for all markets.
     *
     * @param string $pair selected currency pair.
     * Set pair to "all" to get the order books of all markets
     * @return array|object
     */
    public function returnOpenOrders($pair = 'all')
    {
        return $this->performPostRequest([
            'command' => 'returnOpenOrders',
            'currencyPair' => $pair
        ]);
    }

    /**
     * Returns your trade history for a given market or for all markets.
     *
     * @param string $pair selected currency pair.
     * Set pair to "all" to get the order books of all markets
     * @param string $start start date in UNIX timestamp format
     * @param string $end end date in UNIX timestamp format
     * @param integer $limit limit the number of entries returned
     * @return array|object
     */
    public function returnTradeHistory($pair = 'all', $start = null, $end = null, $limit = 500)
    {
        return $this->performPostRequest([
            'command' => 'returnTradeHistory',
            'currencyPair' => $pair,
            'start' => $start,
            'end' => $end,
            'limit' => $limit
        ]);
    }

    /**
     * Returns all trades involving a given order, specified by the "orderNumber" parameter.
     *
     * @param integer $orderNumber
     * @return array
     */
    public function returnOrderTrades($orderNumber)
    {
        return $this->performPostRequest([
            'command' => 'returnOrderTrades',
            'orderNumber' => $orderNumber
        ]);
    }

    /**
     * Returns the status of a given order, specified by the "orderNumber" parameter.
     *
     * @param integer $orderNumber
     * @return object
     */
    public function returnOrderStatus($orderNumber)
    {
        return $this->performPostRequest([
            'command' => 'returnOrderStatus',
            'orderNumber' => $orderNumber
        ]);
    }

    /**
     * Places a limit buy order in a given market.
     *
     * @param string $pair
     * @param integer|float $rate
     * @param integer|float $amout
     * @return object
     */
    public function buy($pair, $rate, $amount)
    {
        return $this->performPostRequest([
            'command' => 'buy',
            'currencyPair' => $pair,
            'rate' => $rate,
            'amount' => $amount
        ]);
    }

    /**
     * Places a sell order in a given market.
     *
     * @param string $pair
     * @param integer|float $rate
     * @param integer|float $amout
     * @return object
     */
    public function sell($pair, $rate, $amount)
    {
        return $this->performPostRequest([
            'command' => 'sell',
            'currencyPair' => $pair,
            'rate' => $rate,
            'amount' => $amount
        ]);
    }

    /**
     * Cancels an order you have placed in a given market.
     *
     * @param integer $orderNumber
     * @return object
     */
    public function cancelOrder($orderNumber)
    {
        return $this->performPostRequest([
            'command' => 'cancelOrder',
            'orderNumber' => $orderNumber
        ]);
    }

    /**
     * Returns your current trading fees and trailing 30-day volume in BTC.
     *
     * @return object
     */
    public function returnFeeInfo()
    {
        return $this->performPostRequest([
            'command' => 'returnFeeInfo'
        ]);
    }

    /**
     * Get a selected currency pair.
     *
     * @param object $ticker the ticker for all markets
     * @param string $pair selected currency pair
     * @return object|null
     */
    private function getPair($ticker, $pair)
    {
        if (isset($pair)) {
            if (property_exists($ticker, $pair)) {
                return $ticker->{$pair};
            } else {
                return null;
            }
        } else {
            return $ticker;
        }
    }

    /**
     * @return void
     */
    private function checkParams($params, &$queryData)
    {
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (isset($value)) {
                    $queryData[$key] = $value;
                }
            }
        }
    }

    /**
     * Execute a GET request.
     *
     * @param string $method
     * @param array $params
     * @return mixed
     */
    private function performGetRequest($method, $params = [])
    {
        $queryData['command'] = $method;
        $this->checkParams($params, $queryData);

        return $this->request(http_build_query($queryData));
    }

    /**
     * Execute a POST request.
     *
     * @param array $params
     * @return mixed
     */
    private function performPostRequest($params = [])
    {
        list($msec, $sec) = explode(' ', microtime());
        $queryData['nonce'] = $sec . substr($msec, 2, 6);
        $this->checkParams($params, $queryData);

        $postFields = http_build_query($queryData);
        $sign = hash_hmac('sha512', $postFields, $this->_secretKey);
        $headers = [
            'Key: ' . $this->_apiKey,
            'Sign: ' . $sign
        ];

        return $this->request($postFields, true, $headers);
    }

    /**
     * Perform a cURL session.
     *
     * @param string $data URL-encoded query string
     * @param boolean $isPost whether this is a POST request
     * @param array $headers an array of HTTP header fields
     * @throws \Exception
     * @return mixed
     */
    private function request($data, $isPost = false, $headers = [])
    {
        if (!$isPost) {
            $options[CURLOPT_URL] = $this->_publicUrl . '?' . $data;
        } else {
            $options[CURLOPT_URL] = $this->_tradingUrl;
            $options[CURLOPT_HTTPHEADER] = $headers;
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $data;
        }

        $curlOptions = [
            CURLOPT_HEADER => false,
            CURLOPT_USERAGENT => $this->_userAgent,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ] + $options;

        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        $result = curl_exec($ch);

        if (($error = curl_error($ch)) !== '') {
            curl_close($ch);
            throw new \Exception($error);
        }
        curl_close($ch);

        return json_decode($result);
    }
}
