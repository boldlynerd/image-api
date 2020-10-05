# image-api
## AWS S3 Image API proof of concept

### Setup:

* composer install
* create .env.local and env.test with needed variables:
    * AWS_S3_ENDPOINT
    * AWS_S3_BUCKET
    * AWS_ACCESS_KEY_ID
    * AWS_SECRET_ACCESS_KEY
    * AWS_S3_REGION
    * AWS_S3_PATHSTYLE_ENDPOINT
* optional variables:
    * IMAGE_SIZE_BYTES_MAX (default 1000000)
    * IMAGE_SIZE_BYTES_MIN (default 400000)
    
### Run unit tests:
` php bin/phpunit`    
    
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