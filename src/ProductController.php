<?php

class ProductController 
{
    public function __construct(private ProductGateway $gateway){

    }
    public function processRequest(string $method, ?string $id): void
    {
        if($id){
            $this->processResourceRequest($method, $id); //products/123
        } 
        else{
            $this->processCollectionRequest($method); //products

        }

    }
    private function processResourceRequest(string $method, string $id):void 
    {
        $product = $this->gateway->get($id);
        if(!$product){
            http_response_code(404); //there is no product with this id
            echo json_encode(["message" => "Product not found"]);
            return;
        }
        switch($method){
            case "GET":
                echo json_encode($product);
                break;
            case "PATCH": //modifying the record
                $data = (array)json_decode(file_get_contents('php://input'), true); //this shows json formatted submision content
                //we added (array) bcs if the submitted data is empty (post wout any data) it will return empty array instead of a null
                
                
                $errors = $this->getValidationErrors($data, false);
                //if errors array is not empty print the error as json and exit the program
                if(!empty($errors)){
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }
                $rows = $this->gateway->update($product, $data); //id of created record

                //result
                echo json_encode([
                    "message" => "Product $id updated",
                    "rows" => $rows

                ]);
                break;
            case "DELETE":
                $rows = $this->gateway->delete($id);
                echo json_encode([
                    "message" => "product $id is deleted",
                    "rows" => $rows
                ]);
                break;
            default:
            http_response_code(405);
            header("Allow: GET, PATCH, DELETE");

        }
        
    }
    private function processCollectionRequest(string $method):void 
    {
        switch($method){
            case "GET":
               echo json_encode($this->gateway->getAll());
               break;
            case "POST":
                $data = (array)json_decode(file_get_contents('php://input'), true); //this shows json formatted submision content
                //we added (array) bcs if the submitted data is empty (post wout any data) it will return empty array instead of a null
                
                
                $errors = $this->getValidationErrors($data);
                //if errors array is not empty print the error as json and exit the program
                if(!empty($errors)){
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $id = $this->gateway->create($data); //id of created record
                
                //changing http response code: 201 for posting data
                http_response_code(201);

                //result
                echo json_encode([
                    "message" => "Product created",
                    "id" => $id

                ]);
                
            default:
                http_response_code(405);
                header("Allow: GET, POST");

        }
    }

    //makes sure that program dont try to send empty data to database
    private function getValidationErrors(array $data, bool $is_new = true): array{
        $errors = [];
        if($is_new && empty($data["name"])){ //name is required for only when creating the record not updating
            $errors[]="name is required";
        }

        if(array_key_exists("size", $data)){
            if (filter_var($data["size"], FILTER_VALIDATE_INT)===false){
                $errors[] = "size must be an integer"; //check if size is an int
            }
        }
        return $errors;

    }
}
// var_dump($data);
// C:\xampp\htdocs\rest-api>curl -X POST -H "Content-Type: application/json" -
// d "{\"name\":\"new\", \"size\":30}" http://localhost/rest-api/products
// array(2) {
//   ["name"]=>
//   string(3) "new"
//   ["size"]=>
//   int(30)
// }
