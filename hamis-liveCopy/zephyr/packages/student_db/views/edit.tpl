<fieldset><legend>Edit an existing record</legend>
<table width="344" border=0>
<tr><td>roll :</td><td><input id='roll' type=text value ='{$std.roll}' readonly="true"></td></tr> 
<tr><td>name :</td><td><input id='name' type=text value ='{$std.name}' ></td></tr> 
<tr><td>class :</td><td><input id ='class' type=text value ='{$std.class}' ></td></tr> 
<tr><td>blood_grp :</td><td><input id='blood_grp' type=text value ='{$std.blood_grp}' ></td></tr> 
<tr><td>&nbsp;</td><td><input type=button value='update' onclick='update_std();' >
<input name="button" type=button onclick="javascript:load_action_smartly('home','','canvas');" value='cancel' ></td></table>
</fieldset>