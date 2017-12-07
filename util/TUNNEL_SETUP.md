# Set Up an SSH Tunnel

Use the example files:
* autossh_example.service 
* ssh_config_example

## Add the customer server to the SSH config file:
```
cat ssh_config_example >> ~/.ssh/config
vi ~/.ssh/config
```

## Create and launch the service
```sh
cp autossh_example.service /etc/systemd/system/autossh_customer.service
cd /etc/systemd/system/
vi autossh_customer.service
systemctl daemon-reload   
systemctl start autossh_customer
systemctl enable autossh_customer
ps -ef | grep ssh
```


