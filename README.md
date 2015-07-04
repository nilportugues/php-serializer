# Serializer for PHP

#### What is serialization? 

    In the context of data storage, serialization is the process of translating data structures 
    or object state into a   format that can be stored (for example, in a file or memory buffer, 
    or transmitted across a network connection link) and reconstructed later in the same or 
    another computer environment.
    
#### Why not `serialize()` and `unserialize()`?

These native functions rely on having the serialized classes loaded and available at runtime and tie your unserialization process to a `PHP` platform.

If the serialized string contains a reference to a class that cannot be instantiated (e.g. class was renamed, moved namespace, removed or changed to abstract) PHP will immediately die with a fatal error.

Is this a problem? Yes it is as now that serialized data is now **unusable**.
