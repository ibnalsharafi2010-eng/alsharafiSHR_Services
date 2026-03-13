<?php

$url="https://customersservices-451af-default-rtdb.europe-west1.firebasedatabase.app/tblusers.json";

$data=file_get_contents($url);

$users=json_decode($data,true);

?>

<table border="1" width="100%">

<tr>
<th>ID</th>
<th>الاسم</th>
<th>المستخدم</th>
<th>الهاتف</th>
<th>البريد</th>
<th>الدور</th>
</tr>

<?php

if($users && is_array($users)){

foreach($users as $id=>$user){

if(!$user) continue;

$id_val = $user['id'] ?? '';
$name = $user['fullName'] ?? '';
$username = $user['username'] ?? '';
$phone = $user['phone'] ?? '';
$email = $user['email'] ?? '';
$role = $user['role'] ?? '';

echo "<tr>";

echo "<td>$id_val</td>";
echo "<td>$name</td>";
echo "<td>$username</td>";
echo "<td>$phone</td>";
echo "<td>$email</td>";
echo "<td>$role</td>";

echo "</tr>";

}

}else{

echo "<tr><td colspan='6'>لا توجد بيانات</td></tr>";

}

?>

</table>