SERP Downloader
=================

Welcome to the SERP Downloader! This is a simple web application that allows you to download search engine result data from Google.


Requirements
------------

This Web Project is made with Nette Framework 3.1 and tested with PHP 8.4. Recommended using with docker.


Installation
------------

To install the Web Project, Docker is recommended. Use the following commands:

	docker compose build
	docker compose run

Ensure the `web/temp/` and `web/log/` directories are writable.


Usage
-----
1) Open the web browser and enter the following URL:
	http://localhost:8080/
2) Follow the given instruction on screen. (Enter the search query you want to download results for in the input field and click the "Download" button.)
3) Results will be downloaded to your computer.