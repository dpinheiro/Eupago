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
            $result = $client->gerarReferenciaMB($data);
        } catch (\SoapFault $sf) {
            die('teste');
            //throw new \Exception($sf->getMessage(), $sf->getCode());
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ? 'http://replica.eupago.pt/replica.eupagov3.wsdl' : 'https://seguro.eupago.pt/replica.eupagov3.wsdl';
    }
}
