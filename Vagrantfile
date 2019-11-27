# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/xenial32"

  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network :private_network, ip: "192.168.68.8"

  config.vm.synced_folder "./", "/var/www/html", owner: "www-data", group: "www-data"
  config.vm.provider "virtualbox" do |vb|
        vb.memory = 16384
        vb.cpus = 1
  end

  config.ssh.insert_key = false

  #config.vm.provision :shell, :path => "config/vagrant/bootstrap.sh"

end