<h1>Introducing basic features of Zephyr</h1>
<table>
	<tr>
		<td width="300" valign="top" >
			<input type="button" class="btn" onclick="load_action1()" value="Load First Action" /><br/>
			<input type="button" class="btn" onclick="load_action2()" value="Load Second Action" /><br/>
			<input type="button" class="btn" onclick="load_image()" value="Load Image" /><br/>
			<input type="button" class="btn" onclick="data_input()" value="Data Form" /><br/><br/>
			<input type="button" class="btn" onclick="input_filter()" value="Input Filter" /><br/>
			<input type="button" class="btn" onclick="output_filter()" value="Output Filter" /><br/><br/>
			<input type="button" class="btn" onclick="execute_embedded_script()" value="Execute Response Script" /><br/><br/>
			<input type="button" class="btn" onclick="setCookie('sample','cookievalue');alert('a cookie named \'sample\' has been set');" value="Set Cookie" /><br/>
			<input type="button" class="btn" onclick="alert('value of that cookie is '+'\'' + getCookie('sample') + '\'')" value="Get Cookie" /><br/>
			<input type="button" class="btn" onclick="deleteCookie('sample');alert('cookie destroyed');" value="Delete Cookie" /><br/><br/>
			<input type="button" class="btn" onclick="load_action_smartly('createdb','','test_result');" value="Create SQLite DB" /><br/>
			<input type="button" class="btn" onclick="load_action_smartly('sqlite_input','','test_result');" value="SQLite Data Entry" /><br/>
			<input type="button" class="btn" onclick="load_action_smartly('report','','test_result');" value="SQLite Data Retreive" /><br/>
			<input type="button" class="btn" onclick="load_action_smartly('aggregator','','test_result');" value="Aggregator Function" /><br/><br/>
			<input type="button" class="btn" onclick="start_cron_action();" value="Run Cron Action" /><br/>			
			
		
 		</td>

		<td valign="top" >
			<div id="test_result" class="resbox">Result Box</div>
		</td>
	</tr>
</table>


