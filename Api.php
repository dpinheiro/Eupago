<?php
namespace dpinheiro\Eupago;

use Http\Message\MessageFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\HttpClientInterface;

class Api
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = array(
        'key' => null,
        'sandbox' => null
    );

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty(array(
            'key'
        ));

        if (false == is_bool($options['sandbox'])) {
            throw new LogicException('The boolean sandbox option must be set.');
        }

        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    public function generateMb(array $params)
    {
        return $this->doRequest($params);
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest(array $fields)
    {
        $data = array(
            "chave" => $this->options['key'],
            "valor" => $fields['value'],
            "id" => $fields['id']
        );

        if (array_key_exists('dateLimit', $fields)) {
            $data['data_fim'] = $fields['dateLimit'];
        }

        $context = stream_context_create([
            'ssl' => [
                // set some SSL/TLS specific options
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        try {
            $client = new \SoapClient($this->getApiEndpoint(), [
                'stream_context' => $context,
                'cache_wsdl'     => WSDL_CACHE_NONE
            ]);
            
            if (array_key_exists('data_fim', $data)) {
                $result = $client->gerarReferenciaMBDL($data);
            } else {
                $result = $client->gerarReferenciaMB($data);
            }

        } catch (\SoapFault $sf) {
            throw new \Exception($sf->getMessage(), $sf->getCode());
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ? 'http://replica.eupago.pt/replica.eupagov3.wsdl' : 'https://seguro.eupago.pt/eupagov3.wsdl';
    }
}
