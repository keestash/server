# -*- mode: ruby -*-
# vi: set ft=ruby :

# see https://github.com/hashicorp/vagrant/issues/12557
Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/groovy64"

  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network :private_network, ip: "192.168.56.1"
  config.vm.provider "virtualbox" do |v|
          v.memory = 8192
          v.cpus = 1
          v.gui = true
      end
  config.vm.synced_folder "./", "/var/www/html", owner: "www-data", group: "www-data"
  config.ssh.insert_key = false

  config.vm.provision :shell, path: "config/vagrant/bootstrap.sh"
end