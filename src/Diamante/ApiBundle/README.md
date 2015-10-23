# DiamanteDesk API Bundle #

API bundle provides extended authentication required for DiamanteDesk API and a way to expose services and their methods as RESTful API.

### Requirements ###

DiamanteDesk supports OroCRM version 1.8+.

### Configuration ###

Add additional configuration to `app/config/security.yml`:

- to section providers:
```yaml
        diamante_api_user:
            id:                             diamante.api.user.security.provider
```

- to section firewalls:
```yaml
        wsse_secured_diamante:
            pattern:                        ^/api/diamante/(rest|soap).*
            provider:                       diamante_api_user
            stateless:                      true
            wsse_diamante_api:              true
```

### Usage ###

For example, you have a service defined in configuration with "entities.service.id" id. To expose it as RESTful API:

- it should implement the following interface \Diamante\ApiBundle\Routing\RestServiceInterface
- the following methods should be public and annotated with \Diamante\ApiBundle\Annotation\ApiDoc:

```php
    /**
     * @ApiDoc(
     *  description="Returns all entities",
     *  uri="/entities.{_format}",
     *  method="GET",
     *  resource=true,
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to list entities"
     *  }
     * )
     * @return Entities[]
     */
```
- specify service in routing configuration:

```
    entities_service:
        resource:     entities.service.id
        type:         diamante_rest_service
        prefix:       /api/rest/{version}/example
        requirements:
            version:  latest|v1
            _format:  xml|json
        defaults:
            version:  latest
        _format:  json
```

After all the steps are completed, your service shall be available at:
 
 ```
 GET http://host/api/rest/latest/example/entities
 ```

## Contributing

We appreciate any effort to make DiamanteDesk functionality better; therefore, we welcome all kinds of contributions in the form of bug reporting, patches submitting, feature requests or documentation enhancement. Please refer to the DiamanteDesk [guidelines for contributing](http://docs.diamantedesk.com/en/latest/developer-guide/contributing.html) if you wish to be a part of the project.
