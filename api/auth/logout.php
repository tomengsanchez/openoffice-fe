<?php
// api/auth/logout.php



// With JWT, logout is handled client-side by destroying the token.
// This endpoint can be used to signal the client to do so.

http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Logout successful. Please discard your token.']);
?>
