<?php

    
	if (isset($_POST['_scandir'])) // fold
	{
		function listFolderFiles($dir, $data) {

		    $ffs = scandir($dir);
		
			foreach ($ffs as $key => $value) {
			    if (strpos($value, '.') === 0) {
			    	unset($ffs[$key]);
			    }
			} 

		    if (count($ffs) < 1)
		        return;

		    foreach($ffs as $ff) { 

		    	$obj = array();

		    	$obj['value'] = $ff;

		    	if (is_dir($dir . '/' . $ff)) {

		    		$obj['data'] = array();

		    		$obj['data'] = listFolderFiles($dir . '/' . $ff, $obj['data']);

		    	} else if ($obj['value'] == "index.js") {
		    	    
		    	    $id = $dir . '/' . $ff;
		    	    $id = str_replace(dirname(__FILE__), '', $id);
		    	    $id = str_replace('/', '', $id);
		            $id = str_replace('.', '', $id);
		    	    //$file = 'out.txt';
                    //file_put_contents($file, $id);    

    		        $output = shell_exec('screen -list | grep ' . $id);
    		    	   
		    	    if (strlen($output) > 0) 
                        $obj['processing'] = true;
		    	    
		    	}

		    	array_push($data, $obj);
		    }

		    return $data;
		}

		$data = array();

		$data = listFolderFiles(dirname(__FILE__), $data); 
	
		echo json_encode($data);
	} 
	
	else if (isset($_POST['_file'])) // fold
	{ 
		if (isset($_POST['_content'])) {

			file_put_contents($_POST['_file'], $_POST['_content']);

		} else {

			echo file_get_contents($_POST['_file']);
		}
	} 
	
	else if (isset($_POST['_createFolder'])) // fold
	{ 
    	shell_exec('mkdir ' . $_POST['_createFolder']);
	} 
	
	else if (isset($_POST['_deleteFolder'])) // fold
	{ 
		shell_exec('rm -rf ' . $_POST['_deleteFolder']);
	} 
	
	else if (isset($_POST['_createFile'])) // fold
	{ 
		shell_exec('echo "" > ' . $_POST['_createFile']);
	} 
	
	else if (isset($_POST['_deleteFile'])) // fold
	{ 
		shell_exec('rm ' . $_POST['_deleteFile']);
	} 
	
	else if (isset($_POST['_toggleNodeServer'])) // fold
	{ 
		$id = $_POST['_toggleNodeServer']; 
		$id = str_replace('/', '', $id);
		$id = str_replace('.', '', $id);
		
		$output = shell_exec('screen -list | grep ' . $id);
    	
	    if (strlen($output) > 0) 
		    shell_exec('screen -S ' . $id . ' -p 0 -X stuff "^C"');
		else
		    // /!\ screen is executed in S-data-www, not in root. Equivalent of "screen-list" from root console would be "ls -laR /var/run/screen/S-www-data"
		    shell_exec('screen -dmS ' . $id . ' node ' . $_POST['_toggleNodeServer']);
	} 
	
    else if (!empty($_FILES['_uploadFile'])) // fold
    {
    	
    	$dossier = $_POST['_path'] . "/";
    	$fichier = basename($_FILES['_uploadFile']['name']);
    	
    	if (move_uploaded_file($_FILES['_uploadFile']['tmp_name'], $dossier . $fichier)) 
	    {
	        
	        echo $fichier;
	    }
	    else
	    {
	        
	        echo 'Error #'.$_FILES['_uploadFile']['error'] .  ' }';
	        
	    }
        
    }
	
	else {	
?>

<!DOCTYPE html>
<html>
   <head>

   		<link rel="stylesheet" href="//cdn.webix.com/edge/webix.css" type="text/css">
       	<script src="https://cdn.webix.com/edge/webix.js" type="text/javascript"></script>
       	
       	<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
       	<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.min.js"></script>
       	
       	<link rel="stylesheet" href="//cdn.materialdesignicons.com/5.3.45/css/materialdesignicons.min.css">
       	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
       	
       	<!--
       	<form id="uploadForm" action="?" style="visibility:hidden; position:absolute" method="post">
            <input type="file" id="fileUploader" name="_uploadFile">
        </form>
        -->
        
        
        <input type="file" id="fileUploader" style="visibility:hidden; position:absolute" name="file">
        

       	<style>
    
       		#parent {
			    width:100%;
			    height:100%;
			    position: absolute;
			}
		
			#editor {
			    position: absolute;
			    top: 0;
			    right: 0;
			    bottom: 0;
			    left: 0;
			    margin-left:-12px;
			    margin-right:-12px;
			    margin-bottom:4px;
			    margin-top:-6px;
			}

			.webix_message_area {
			  	top:15px !important;
			  	left:315px !important;
			}

			.webix_tree {
				background-color: #F4F7FA;
				margin-right: -5px;
				color:black;
			}

			.my_normal {
				background-color: #F4F7FA;
				color:black;
			}

			.my_selected  {
	            background-color: #c7cbd1;
	            color:black;
	            margin-left: -150px;
	            padding-left: 150px;
	            margin-right: -150px;
	            padding-right: 150px;
	        }

	        .my_hover {
	        	background-color: #E4E5F0;
	        	margin-left: -150px;
	            padding-left: 150px;
	            margin-right: -150px;
	            padding-right: 150px;
	        }
	        
	        .webix_view.webix_control .webix_disabled_box textarea {
                color:black;
	        }
	        
	        @-webkit-keyframes rotation {
    		    from {
    				-webkit-transform: rotate(0deg);
    		    }
    		    to {
    				-webkit-transform: rotate(359deg);
    		    }
            }
		
		</style>
		
   </head>

   <body>

       <script type="text/javascript">
       
            // fonction d'upload (clic droit sur un folder)
            document.getElementById("fileUploader").onchange = function() // fold
            {
               
                var fileInput = document.getElementById('fileUploader');
                var file = fileInput.files[0];
                console.log(file);
                
                var formData = new FormData();
                formData.append('_uploadFile', file, file.name);
                formData.append('_path', contextPath);
           
                
                $.ajax({
			    	type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
	       			
	       				$$('sideMenu').data.add({value:response}, 0, contextId ); // (0 : en haut du folder)
						$$('sideMenu').sort("#value#", "asc"); //sorts all nodes (parent, child)
						
	       				webix.message(response + ' uploaded successfully');
			    	}
			  	});
			  	
                
                
            };

       
            function rootOf(itm) // fold
            {
                while (itm.$parent != "0") {
                    itm = $$('sideMenu').getItem(itm.$parent);
                }	
              
                return itm.value;
            }


            // based on item id, return path of the item 
			function pathOfId(id) // fold
			{
				let item = $$('sideMenu').getItem(id);

				let path = item.value;

		    	while (item.$parent != "0") {
		    		item = $$('sideMenu').getItem(item.$parent);
		    		path = item.value + '/' + path;
		    	}	

		    	return path;
			}
            
                
       		let hovered = null;
       		let selected = null;
       		let context = false;
       		let contextPath = null;
       		let contextId = null;
       		
       		// bug : apres un open in ew tab, selected n'est plus selected
      
	       	webix.ready(function () {
	       	            
	       	     // redirects console of test window to right pannel of the window
                function print(msg) // fold
                { 
           		    let currentText = $$("externConsole").getValue();
           		    $$("externConsole").define("value", currentText  + "\n" + msg);
                    $$("externConsole").refresh();
                }  
    
	       		// Ctrl key actions
	       		document.addEventListener("keydown", function(e) // fol
	       		{
				  	if (window.navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey) {
                        
                        // s for save
                        if (e.keyCode == 83) // fold
                        {
                            
                            $.ajax({
    					    	type: "POST",
    					    	data : { 
    					    		_file : pathOfId(selected),
    					    		_content : editor.getValue()
    					    	},
    					    	success: function(response) {
    					    		webix.message("Saved");
    					    	}
    					  	});
    					  	
    					 	editor.session.autoFold();
    
    				    	e.preventDefault();
                            
                        // b pour build (ouvre index dans un autre onglet, si on est sur un index)
                        // si on est sur un server.js, lance node
                        } 
                        
                        // b for build
                        else if (e.keyCode == 66) // fold
                        {
                            let itm = $$('sideMenu').getItem(selected);
                            
                            let level = itm.$level;
                            
                            let root = rootOf(itm);
                            
                            // ctrl shit b for node 
                            if (e.shiftKey) {
                                
                                let res = $$("sideMenu").find(function(obj) {
                                    return (obj.value === "index.js") && (rootOf(obj).indexOf(root) != -1) && (obj.$level <= level);
                                }, false);
                                
                                if (res.length == 0) {
                                    
                                    webix.message("index.js could not be fouund in " + root);
                                    
                                } else if (res.length == 1) {
                                    
                                    $.ajax({
								    	type: "POST",
								    	data : { _toggleNodeServer : pathOfId(res[0].id) },
								    	success: function(response) {
								    	    if (res[0].processing)
								    		    webix.message('Node server stopped successfully');
								    		else 
								    		    webix.message('Node server started successfully');
								    		res[0].processing = !res[0].processing;
									        $$('sideMenu').updateItem(res[0].id, res[0]);
								    	}
								  	});
                                    
                                } else {
                                    
                                    webix.message("Too many index.js files in " + root);
                                    
                                }
                                
                            } else {
                                
                                let res = $$("sideMenu").find(function(obj) {
                                    return ((obj.value === "index.html") || (obj.value === "index.php")) && (rootOf(obj).indexOf(root) != -1) && (obj.$level <= level);
                                }, false);
                                
                                if (res.length == 0) {
                                    
                                    webix.message("index.php or index.html could not be found in " + root);
                                     
                                } else if (res.length == 1) {
                                    
                                    let path = pathOfId(res[0].id);
                                    
                                    let html = "<iframe style='margin-top:-4px; margin-left:-12px' id='muhFrame' " +
                                        "width='" + (window.innerWidth - 250) + "' height='" + window.innerHeight + "' frameBorder='0' " +
                                        "src=" + window.location.protocol + "//" + window.location.hostname + "/" + path + "> </iframe>";
               
                                    $$('mainWindow').setHTML(html);
                                    
                                    $$("externConsole").define("value", "");
                                    $$("externConsole").define("height", window.innerHeight);
                                    $$("externConsole").refresh();
                                    
                                    $$('dev').hide();
                                    $$('test').show();
                                    
                                } else {
                                    
                                    webix.message("Too many index.html or index.php files in " + root);
                                }
                            }
                            
                            e.preventDefault();
                        
                        } 
                        
                        // ctrl / cmd only (no letter) 224 irefox, 91 brave
                        else if (e.keyCode == 224 || e.keyCode == 91) // fold
                        {
                            $$('dev').show();
                            $$('test').hide();
                            
                            $$('mainWindow').setHTML("");
                        }
				  	    
				  	}	
	       		}, false);
                
                
                // Right click on left menu (context)
				webix.ui({
			  		view: "contextmenu",
			  		id: "contextMenu",
			  		autowidth:true,
			  		on: {

			  			onHide : function() // fold
                        {
			  				context = false;
                        },

			    		onItemClick : function (id, e, node) // fold
			    		{
					       	if (id == "New folder") // fold
					        {

					       		webix.prompt({
								  	title: "New folder",
								  	text: "Please type a name :",
								  	ok: "Create",
								  	cancel: "Cancel",
								  	width:350,
								}).then(function(result) {
								  	
								  	
									$.ajax({
								    	type: "POST",
								    	data : { _createFolder : contextPath + "/" + result },
								    	success: function(response) {
								    		webix.message(result + ' successfully created');
								    		$$('sideMenu').data.add({value:result}, 0, contextId ); // (0 : en haut du folder)
								    		$$('sideMenu').sort("#value#", "asc"); //sorts all nodes (parent, child)

								    	}
								  	});


								});

								// set focus to input in the modal box
								var count = webix.modalbox.order.length;
								var lastModalbox = webix.modalbox.pull[webix.modalbox.order[count - 1]];
								if (lastModalbox) 
									lastModalbox._box.querySelector("input").focus();

					       		
					        } 
					       	
					       	else if (id == "New file") // fold
                            {

					       		
					       		webix.prompt({
								  	title: "New file",
								  	text: "Please type a name :",
								  	ok: "Create",
								  	cancel: "Cancel",
								  	width:350,
								}).then(function(result) {

									$.ajax({
								    	type: "POST",
								    	data : { _createFile : contextPath + "/" + result },
								    	success: function(response) {
								    		webix.message(result + ' successfully created');

								    		$$('sideMenu').data.add({value:result}, 0, contextId ); // (0 : en haut du folder)
								    		$$('sideMenu').sort("#value#", "asc"); //sorts all nodes (parent, child)

								    	}
								  	});
								  	

                                });

								// set focus to input in the modal box
								var count = webix.modalbox.order.length;
								var lastModalbox = webix.modalbox.pull[webix.modalbox.order[count - 1]];
								if (lastModalbox) 
									lastModalbox._box.querySelector("input").focus();
								
                            } 
					       	
					       	else if (id == "Delete") // fold
					       	{
					  			
					       		if (contextPath.includes(".")) {

					       			let fileName = contextPath.replace(/^.*[\\\/]/, '')

					       			webix.confirm({
								    	width:350,
								    	ok:"Delete", 
								    	cancel:"Cancel",
								    	title:"Delete " + fileName + " ?"
									}).then(function(result) {

										$.ajax({
									    	type: "POST",
									    	data : { _deleteFile : contextPath },
									    	success: function(response) {
							       				webix.message(fileName + ' successfully deleted');
    											$$('sideMenu').remove(contextId); 
									    	}
									  	});
									});

					       		} else {

					       			let folderName = contextPath.replace(/^.*[\\\/]/, '')

					       			webix.confirm({
								    	width:350,
								    	ok:"Delete", 
								    	cancel:"Cancel",
								    	title:"Delete " + folderName + " ?"
									}).then(function(result) {

										$.ajax({
									    	type: "POST",
									    	data : { _deleteFolder : contextPath },
									    	success: function(response) {
							       				webix.message(folderName + ' successfully deleted');
							       				$$('sideMenu').remove(contextId); 
									    	}
									  	});
									});


					       		}

					       		

					       		
					       	} 
					       	
					       	else if (id == "Rename") // fold
					       	{
					  			
					       		if (contextPath.includes(".")) // fold
					       		{

					       		

					       		} else {

					       			
					       		}



					       		
					       	} 
					       	
					       	else if (id == "Open (^T)") // fold
					       	{
    							window.open(contextPath, '_blank');
								//window.open(contextPath);
					       	}
					       	
					       	else if (id == "Upload file") // fold
					       	{
					       	    
					       	    var uploader = document.getElementById("fileUploader");
                                uploader.click();
                    
					       	}

					       	hovered = null;
       						selected = null;
			    		}
			  		}
				});
                
                // left menu (tree)
	            webix.ui({ 
	                
	                margin:0,
	                padding:0,
	                rows:[
	                    
	                    {
	                        id:"dev",
	                        cols : [
        	             		{
        	             			id:'sideMenu',
        	             			scroll:"auto",
        					   	 	width:250,					
        							view:"tree",
        							activeTitle:true,
        
        						    template:function(obj, com) // fold
        						    {
        						        let icon;
        						        
        						        if (obj.value.includes(".")) {
        						            if (obj.processing == true) // add processing value for node servers
    						                    icon = "<span class='webix_icon mdi mdi-image-filter-vintage' style='float:left; margin:3px 4px 0px 1px; animation: rotation 2s infinite linear;'></span>";
        						            else
    						            	    icon = "<span class='webix_icon mdi mdi-text-box-outline' style='float:left; margin:3px 4px 0px 1px;'></span>";
    						            } else {
    						                if (obj.open == true) 
    						            		icon = "<span class='webix_icon mdi mdi-folder-open' style='float:left; margin:3px 4px 0px 1px;'></span>";
    						            	else
    						                	icon = "<span class='webix_icon mdi mdi-folder' style='float:left; margin:3px 4px 0px 1px;'></span>";
    						            }
    						            
                                        return com.icon(obj, com) + icon + obj.value;
        						    },  
       
        						    on:{
        
        								onItemClick : function(id, e, node) // fold
        								{
        									if (context == true) {
        										$$('contextMenu').hide();
        										e.preventDefault();
        										return false;
        									}
        
        								    var item = this.getItem(id);
        								    
        								        //console.log(item.value);
        
        								    // si l'item cliqué n'est pas un dossier (dc un fichier)
        								    if (item.value.includes(".")) {
										    
										    document.title = item.value;
        
        								    	// je déselect l'ancien selected
        								    	if (selected != null) {
        								    		let obj = this.getItem(selected);
        											obj.$css = "my_normal";
        											this.updateItem(selected, obj);
        								    	}
        								   
        								    	item.$css = "my_selected";
        								    	this.updateItem(id, item);
        
        								    	selected = id;
        								    	hovered = null;
        
        								    	let path = item.value;
        
        								    	while (item.$parent != "0") {
        								    		item = this.getItem(item.$parent);
        								    		path = item.value + '/' + path;
        								    	}	
        
        								    	$.ajax({
        
        									    	type: "POST",
        									    	data : { _file : path },
        									    	success: function(response) {
        
        										    	editor.session.setValue(response);
        
        										    	let mode = "ace/mode/";
        										    	let ext = path.split('.').pop();
        
        											    if (!ext)
        											        mode += "text";
        											    
        											    switch (ext) {
        											        case "js":
        											        	mode += "javascript";
        											        	break;
        											        case "html":
        											        	mode += "html";
        											        	break;
        											        case "php":
        											        	mode += "php";
        											        	break;
        											    }
        
        												editor.session.setMode(mode);
        												editor.session.autoFold();
        									    	}
        									  	});
        								    }
        								},	
        
        								onMouseMoving : function(ev) // fold
        								{
        
        									if (context == true) 
        										return;
        										
        									var id = this.locate(ev);
        
        									if (id == selected) {
        
        										if (hovered != null) {
        
        											let obj = this.getItem(hovered);
        											obj.$css = "my_normal";
        											this.updateItem(hovered, obj);
        
        										}
        
        										hovered = null;
        
        										return;
        									}
        										
        									if (id != hovered) {
        
        										if (hovered != null) {
        
        											let obj = this.getItem(hovered);
        											obj.$css = "my_normal";
        											this.updateItem(hovered, obj);
        
        										}
        
        										if (id != null) {
        
        											var item = this.getItem(id);
        
        											if (item.$css != "my_selected") {
        												item.$css = "my_hover";
        										    	this.updateItem(id, item);
        										   	}
        										}
        
        										hovered = id;
        
        									}
        								}, 	
        								
        								onBeforeContextMenu : function(id, e, node) // fold
        								{
        
        									if (context == true) {
        										$$('contextMenu').hide();
        										e.preventDefault();
        										return false;
        									}
        
        									contextPath = pathOfId(id);
             								
             								contextId = id;
        
             								$$('contextMenu').clearAll(); // clear data
        
             								if (contextPath.includes(".")) {
             									
        							  			
        							  			if (contextPath.includes("index.")) {
        							  			    
        							  			    $$('contextMenu').parse([
            								  			"Open (^T)",
            								  			{ $template:"Separator" },
            								  			// "Rename",
            								  			"Delete",
            								  			// { $template:"Separator" },
            								  			// "Download"
            							  			]);
        							  			
        							  			
        							  			} else {
        							  			    
        							  			    $$('contextMenu').parse([
            								  			// "Rename",
            								  			"Delete",
            								  			// { $template:"Separator" },
            								  			// "Download"
            							  			]);
        							  			}
        							  			
        
             								} else {
        
             									$$('contextMenu').parse([
             									    "Upload file",
             										"New file",
             										// "Rename",
             										
        								  			"New folder", 
        								  			{ $template:"Separator" },
        								  			"Delete",
        								  			// { $template:"Separator" },
        								  			// "Download"
        								  		]);
             								}
        
        									context = true;
        
        								}
        							}
        					  	},
        
        						{
        							view:'template',
        							template:'<div id="parent"><div id="editor"></div></div>',
        						},
        	             	],
	                        
	                    },
	                    
	                    {
	                        id:'test',
	                        hidden:true,
	                        cols:[
                                {
                                    id:'mainWindow',
                                    view:'template',
                                    template:"",
                                    on:{
                                        
                                        // console redirecting
                                        onAfterRender:function() // fold
                                        {
                                            var iframe = document.getElementById('muhFrame');
                            
                                            var tmp = iframe.contentWindow;
                                            
                                            tmp.console.log = function(val) {
                                                print("Log: " + val);
                                            };
                                            
                                            tmp.onerror = function(message, url, line) {
                                                
                                                var filename = url.split("/").pop();
 
                                                print("Error: " + message + " (" + filename + ":" + line +")");
                                                
                                                return false;
                                            }
                                        }
                                    }
                                },
                                {
                                    view:"textarea",
                                    width:250,
                                    id:"externConsole",
                                  	label:"",
                                  	borderless:true,
                                }
                            ]
	                    }
	                ]
		        });
		        
		        $$("contextMenu").attachTo($$("sideMenu"));

		        var editor = ace.edit("editor");
		        ace.require("ace/ext/language_tools");
		     	editor.setTheme("ace/theme/monokai");
		     	editor.setOption("showPrintMargin", false);
                editor.setOption("enableLiveAutocompletion", true);
                
				editor.session.__proto__.autoFold = function() // fold
				{
				    // folds imbriqués : il ne prend que celui de plus haut niveau
				    // si on veut pallier cela, il faudrait faire un deuxième for qui dépile les lignes pour chaaque // fold trouvé
				    
					let start = null;
					let indentation = "";

					for (let row = 0; row < this.getLength(); row++) {

						if (this.getLine(row).includes("// fold")) {

							let match = this.getLine(row+1).match(/([\s]*){/);

							if (match != null && start == null) {
							    if (match[1] != null)
								    indentation = match[1];
								else
								    indentation = "";
								
								start = row;
							}
							
						} else if (this.getLine(row).startsWith(indentation + "}")) {

				            if (start != null) {

								let Range = require('ace/range').Range,
			    				mine = new Range(start, this.getLine(start).length-7, row, this.getLine(row).length);
								this.addFold("...", mine);

								start = null;
							}
						}
					}
				}
				
				// at beginning, load all files of /var/www/html and display them in tree on left menu
				$.ajax( // fold
			    {
			    	type: "POST",
			    	data : { _scandir : true },
			    	success: function(response) {
			    		$$('sideMenu').parse(response);
			    		$$('sideMenu').sort("#value#", "asc");
			    	}
			    });
	     	
	       	});
	     	
       	</script>
	</body>
</html>

<?php
	}
?>
