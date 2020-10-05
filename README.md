# image-api
## AWS S3 Image API proof of concept

### Docker:
docker-compose up --build

Access using http://localhost

### Setup:
* composer install
* create imageApi/.env.local
    * AWS_S3_ENDPOINT (if using docker: http://localstack:4566)
    * AWS_S3_BUCKET
    * AWS_ACCESS_KEY_ID
    * AWS_SECRET_ACCESS_KEY
    * AWS_S3_REGION
    * AWS_S3_PATHSTYLE_ENDPOINT
* optional variables:
    * IMAGE_SIZE_BYTES_MAX (default 1000000)
    * IMAGE_SIZE_BYTES_MIN (default 400000)
    
### Run unit tests:
* create imageApi/env.test (see Setup)
    * AWS_S3_ENDPOINT (if using docker: http://localhost:4566)
* cd into imageApi
* `php bin/phpunit`    
    
### Endpoints:
####Upload one image to S3
`/api/images/ (POST - multipart form data)`

Params:
* userName | url-friendly string
* imageName | url-friendly string
* image | image file

Return value (Json)
* success | bool
* url | string

####Return the presigned S3 URL for one image
`/api/images/urls/{userName}/{imageName} (GET)`

Params:
* userName | url-friendly string
* imageName | url-friendly string

Return value (Json)
* success | bool
* url | string

####Return the list of image names for one user
`/api/images/user/{userName} (GET)`

Params:
* userName | url-friendly string

Return value (Json)
* success | bool
* images | array of strings

####todo (general): 
* document list of errors + error codes
* exception classes
* group unit tests