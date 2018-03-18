# xe-something
This is branch module.
web site : https://xe-something.com

## Coding standard

##### Variable names.
Variable name use to camel casing.

e.g) $userId = 블라블라.

But, Allows some underscore bars for compatibility.

e.g) $userId = Context::get('user_id'); // member_srl, member_id ETC, if use to `Context::get` you can underscore bars. 


##### Funcion names.
Funcion name use to camel casing. 

e.g) public function getMemberInfoByUserId($user_id)
