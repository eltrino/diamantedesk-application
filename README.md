DiamanteDesk Api Bundle
========================

## Configuration

app/config/security.yml

Change providers section to this one:

```
    providers:
        chain_provider:
            chain:
                providers:                  [in_memory, oro_user]

        api_chain_provider:
            chain:
                providers:          [in_memory, diamante_api_user, oro_user]

        diamante_api_user:
            id:                             diamante.api.user.security.provider
        oro_user:
            id:                             oro_user.security.provider
        in_memory:
            memory:
                users:                      []

    encoders:
        Oro\Bundle\UserBundle\Entity\User: sha512
        Diamante\ApiBundle\Entity\ApiUser: sha512
        Symfony\Component\Security\Core\User\User: plaintext

    firewalls:
        dev:
            pattern:                        ^/(_(profiler|wdt)|css|images|js)/
            security:                       false

        wsse_secured:
            pattern:                        ^/api/(rest|soap).*
            provider:                       api_chain_provider
            wsse:
                lifetime:                   3600
                realm:                      "Secured API"
                profile:                    "UsernameToken"
            context:                        main
```
             