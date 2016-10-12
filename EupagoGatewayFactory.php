<?php
namespace dpinheiro\Eupago;

use dpinheiro\Eupago\Action\AuthorizeAction;
use dpinheiro\Eupago\Action\CancelAction;
use dpinheiro\Eupago\Action\ConvertPaymentAction;
use dpinheiro\Eupago\Action\CaptureAction;
use dpinheiro\Eupago\Action\NotifyAction;
use dpinheiro\Eupago\Action\RefundAction;
use dpinheiro\Eupago\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class EupagoGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'eupago',
            'payum.factory_title' => 'Eupago',
            'payum.action.capture' => new CaptureAction(),
            //'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'key' => '',
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['key'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api(
                    array(
                        'key' => $config['key'],
                        'sandbox' => $config['sandbox']
                    ),
                    $config['payum.http_client'],
                    $config['httplug.message_factory']
                );
            };
        }
    }
}
