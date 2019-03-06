<?php
namespace dpinheiro\Eupago\Action;

use dpinheiro\Eupago\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Payment;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;

class CaptureAction extends GatewayAwareAction implements ApiAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var Api
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }
        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        //$httpRequest = new GetHttpRequest();
        //$this->gateway->execute($httpRequest);

        $data = array(
            'value' => $model['AMOUNT'] / 100,
            'id'    => $model['ORDER_ID']
        );

        if (array_key_exists('DATE_LIMIT', $model)) {
            $data['dateLimit'] = $model['DATE_LIMIT'];
        }

        $result = $this->api->generateMb($data);

        $result->valor = $result->valor * 100;
        $result->valor_minimo = $result->valor_minimo * 100;
        $result->valor_maximo = $result->valor_maximo * 100;

        $model->replace((array) $result);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
