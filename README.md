# UserBundle
Generic User Bundle for Symfony
---
#app/config/security.yml:
```
security:
    access_control:
        - { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/register, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/$, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        # - { path: ^/admin, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        # - { path: ^/, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/, roles: ROLE_USER }

    encoders:
        Lthrt\UserBundle\Entity\User:
            algorithm: bcrypt
            cost: 13
        Symfony\Component\Security\Core\User\User: plaintext

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        local:
            anonymous: ~
            host: localhost
            provider: chain_provider
            form_login:
                check_path: /login
                login_path: /login
            logout:
                path:   /logout
                target: /login
            pattern:    ^/
        main:
            anonymous: ~
            provider: lthrt_user_provider
            form_login:
                check_path: /login
                login_path: /login
            logout:
                path:   /logout
                target: /login
            pattern:    ^/

    providers:
        chain_provider:
            chain:
                providers: [ lthrt_user_provider, in_memory ]
        in_memory:
            memory: 
                users:
                    <username>:
                        password: "%<parameter>%"
                        roles: '<role>'

        lthrt_user_provider:
            entity:
                class: Lthrt\UserBundle\Entity\User
     
    role_hierarchy:
        ROLE_ADMIN: ROLE_USER
        ROLE_DEV: ROLE_ADMIN
```
---
#app/config/services.yml:
```
imports:
    - { resource: "@Lthrt/UserBundle/Resources/config/services.yml" }

services:
    _defaults:
        # automatically injects dependencies in your services
        autowire: true
```
---
#app/config/routing.yml:
```
lthrt_user:
    resource: "@LthrtUserBundle/Controller/"
    type:     annotation
```
---
#app/config/config_test.yml
```
doctrine:
    dbal:
        connections:
            default:
                driver: pdo_sqlite
                path: dbtest
                logging: true
                profiling: false
```
#app/config/config.yml
```
lthrt_user:
    login_data_length: 10     # This is the number of entries to keep per user (10 default)
```
---
.gitignore
```
dbtest
```
phpunit.xml.dist
---
```
<testsuites>
    <-- add to testsuites section -->
    <testsuite name="LthrtUser">
        <directory>vendor/lthrt/user-bundle/Lthrt/UserBundle/Tests</directory>
    </testsuite>
</testsuites>
```
---
app/AppKernel.php
```
 ...   
    public function registerBundles()
    {
        $bundles = [
            ...

            new Lthrt\UserBundle\LthrtUserBundle(),
            
            ...
        ];

        ...
    }
...