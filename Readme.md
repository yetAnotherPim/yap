Config:
max_input_vars=10000


console messenger:consume scheduler_default --time-limit=3600

Configure via Supervisor:
https://symfony.com/doc/current/messenger.html#messenger-supervisor
Command: php /path/to/your/app/bin/console messenger:consume scheduler_default --time-limit=3600

Configure via Systemd:
https://symfony.com/doc/current/messenger.html#systemd-configuration
ExecStart=php /path/to/your/app/bin/console messenger:consume scheduler_default --time-limit=3600

TODO:
- setup
- product_update queue or js consumer
