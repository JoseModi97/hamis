<table width="100%" >
  <tr>
    <th>Name</th>
    <th>Roll</th>
    <th>Age</th>
  </tr>
{foreach item=std from=$students}
  <tr>
    <td>&nbsp;{$std.name}</td>
    <td>&nbsp;{$std.roll}</td>
    <td>&nbsp;{$std.age} Year</td>
  </tr>
  {/foreach}
</table>