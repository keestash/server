# Keestash
Open Source Password Safe

![](https://keestash.com/wp-content/uploads/2019/10/keestash_logo_inverted_no_name.png|width=50)

* **encrypt your passwords** securely in your own password safe
* **share with whoever you want** your team, familiy or just friends
* **easily extensible** thanks to it's app eco system

You want to learn more about Keestash?(https://keestash.com).

## Get Keestash

* **Install on your own server**
* **Register for a Keestash Account** (coming soon)
* **Enterprise Hosting** for teams, public organizations or education (coming soon)

 ### Development Setup
 
 * download and install Vagrant
 * run `vagrant up` in the root folder
    * Vagrant installs a virtual machine set up with a database and webserver
 * open your favorite web browser and type in the IP address and port specified in Vagrantfile (default: 192.168.68.8:80)
 * alternatively, install a database and webserver and run the phinx migrations
 * run `composer install`
 * run `npm install && npm run watch:dev`
