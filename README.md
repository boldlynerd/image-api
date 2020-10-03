# image-api
## AWS S3 Image API proof of concept

### Setup:

* composer install
* create .env.local with needed variables:
    * AWS_S3_ENDPOINT
    * AWS_S3_BUCKET
    * AWS_ACCESS_KEY_ID
    * AWS_SECRET_ACCESS_KEY
    * AWS_S3_REGION
    * AWS_S3_PATHSTYLE_ENDPOINT
    
### Endpoints:
####Upload one image to S3
`/api/images/ (POST)`

Params:
* userName | url-friendly string
* imageName | url-friendly string
* base64Image | base64 encoded image

####Return the S3 URL for one image
`/api/images/urls/{userName}/{imageName} (GET)`

####Return the list of image names for one user
`/api/images/user/{userName} (GET)`
