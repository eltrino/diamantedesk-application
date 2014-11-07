DiamanteDesk Api Bundle
========================

## Configuration

app/config/security.yml

Change providers section to this one:

```
providers:
    chain_provider:
        chain:
            providers:                  [in_memory, diamante_api_user, oro_user]

    diamante_api_user:
        id:                             diamante.api.user.security.provider
    oro_user:
        id:                             oro_user.security.provider
    in_memory:
        memory:
            users:
```
             