<div id="canvas">
<h3>Student database</h3>
<fieldset>
<legend>Insert a new record</legend>
<table width="252" border=0>
<tr><td>roll :</td><td><input id='roll' type=text value ={$std.roll} ></td></tr> 
<tr><td>name :</td><td><input id='name' type=text value ={$std.name} ></td></tr> 
<tr><td>class :</td><td><input id ='class' type=text value ={$std.class} ></td></tr> 
<tr>
  <td>blood group:&nbsp;</td>
  <td><input id='blood_grp' type=text value ={$std.blood_grp} ></td></tr> 
<tr><td>&nbsp;</td><td><input type=button value='insert student' onclick='insert_std();' ></td></table>
</fieldset><br />

<div id="stdlist">
<fieldset><legend>all students</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="5" class="lb">
  <tr>
    <td width="23%"><strong>Name</strong></td>
    <td width="20%"><strong>Roll</strong></td>
    <td width="23%"><strong>Class</strong></td>
    <td width="20%"><strong>Blood Group </strong></td>
    <td width="14%"><strong>Action</strong></td>
  </tr>
  {foreach item=std from=$students}
  <tr>
    <td>&nbsp;{$std.name}</td>
    <td>&nbsp;{$std.roll}</td>
    <td>&nbsp;{$std.class}</td>
    <td>&nbsp;{$std.blood_grp}</td>
    <td><a href="javascript:load_action_smartly('edit','{$std.roll}','canvas');">Edit</a> | <a href="javascript:delete_std('{$std.roll}');">Delete</a>  </td>
  </tr>
  {/foreach}
</table>
</fieldset>
</div>
</div>