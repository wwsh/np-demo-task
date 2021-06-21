# Secure Information Storage REST API

### Project setup

* Add `secure-storage.localhost` to your `/etc/hosts`: `127.0.0.1 secure-storage.localhost`

* Run `make init` to initialize project

* Open in browser: http://secure-storage.localhost:8000/item Should get `Full authentication is required to access this resource.` error, because first you need to make `login` call (see `postman_collection.json` or `SecurityController` for more info).

### Run tests

make tests

### API credentials

* User: john
* Password: maxsecure

### Postman requests collection

You can import all available API calls to Postman using `postman_collection.json` file

### Reference

This API supports the following requests:

* `POST /login` - authenticates user with given username and password 
  
* `POST /logout` - removes user authentication and closes session 
  
* `GET /item` - retrieves items with data for currently logged in user
  
* `POST /item` - creates a new item with data for the current user 

* `PUT /item/{id}` - updates data for the given item's id 

* `DELETE /item/{id}` - removes item completely for the current user 



