## File Access


As of version 2.0, @{service FileAccess} can break a file into chunks.  Smaller chunks may speed uploads or help
parallelize certain operations.

@{include examples/file_chunking.raw}

This example demonstrates how to break up a larger file into chunks.  By default, 2MB chunks are created, 
but for the purpose of this demo, the chunk size is 64KB.
