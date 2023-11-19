# Keestash
Open Source Password Manager

![](https://keestash.com/wp-content/uploads/2019/10/keestash_logo_inverted.png)

* **encrypt your passwords** securely in your own password manager
* **share with whoever you want** your team, familiy or just friends
* **easily extensible** thanks to it's app eco system

You want to learn more about Keestash?(https://keestash.com).

## Get Keestash

* **Install on your own server**
* **Register for a Keestash Account** (https://app.keestash.com)
* **Enterprise Hosting** for teams, public organizations or education. [Get in touch with us](https://ucar-solutions.de), we will create an offer for your needs!

 ### Development Setup
 
 #### !! Keestash switched local development from Vagrant to Docker !!

* install Docker on your system.
* Use the `docker compose up` command in the root folder to create a Docker container.
* Run several commands within the Docker container in order:
  * Empty the `config/config.php` file if it exists, then use `php ./bin/console.php keestash:install:config` to add the required configuration parameters. 
  * Execute `php ./vendor/bin/phinx migrate -c config/phinx/instance.php` to migrate core databases.
  * Run `php ./vendor/bin/phinx migrate -c config/phinx/apps.php` to migrate app databases.
  * Use `php ./bin/console.php keestash:create-system-user` to create a system user.
  * Use `php ./bin/console.php install:instance-data` to add instance-specific configurations.
    * In certain scenarios, if you encounter an error stating id and hash exists, ignore this step.
  * Execute `php ./bin/console.php permission:create` to create permissions.
  * Run `php ./bin/console.php permission:role:create` to create all roles.
  * Use `php ./bin/console.php register:create-user` to create a user account.
  * Run `php ./bin/console.php permission:role:assign-all` to assign all roles to the created user(s).
    * Make sure users are created before this step.
  * Use `php ./bin/console.php keestash:environment:add --force environment dev` to set the environment to `dev` for Keestash.
  * Execute `php ./bin/console.php keestash:environment:add --force allowed.send.notifications false` to disable sending of notifications (like email).
  * Start a daemon service by running `php ./bin/console.php keestash:worker:run` for asynchronous operations.

Keestash setup is complete and ready to use if none of the above steps yield an error. It is now possible to interact with the server using any available client.

 * Official Keestash Frontend (Link coming soon)