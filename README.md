# Eupago payum gateway

## Symfony

### Installation

```yaml
services:
    app.eupago.gateway_factory_builder:
        class: Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder
        arguments:
            - dpinheiro\Eupago\EupagoGatewayFactory
        tags:
            - { name: payum.gateway_factory_builder, factory: eupago }
```

### Configuration

```yaml
payum:
    gateways:
        eupago:
            factory: eupago
            key: %eupago_key%
            sandbox: %eupago_sandbox%
```