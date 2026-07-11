<?php 
include (dirname(__FILE__) . '/header.php');
include (dirname(dirname(__FILE__)) . "/objects/class_services.php");
include (dirname(__FILE__) . '/user_session_check.php');
$con = new cleanto_db();
$conn = $con->connect();
$objservice = new cleanto_services();
$objservice->conn = $conn; ?>
<div id="cta-clean-services-panel" class="panel tab-content">    
    <div class="panel-body">        
        <div class="ct-clean-service-details tab-content col-md-12 col-sm-12 col-lg-12 col-xs-12">

    <!-- right side common menu for service -->            
    <div class="ct-clean-service-top-header">                
        <span class="ct-clean-service-service-name pull-left"><?php echo $label_language_values['all_services']; ?>(<?php echo $objservice->countallservice(); ?>)</span>                
            <div class="pull-right">                    
                <table>                        
                    <tbody>                        
                        <tr>                            
                            <td>                                
                                <?php $isrecord = $objservice->getalldata();
                                if ($isrecord)
                                    {
                                         $cnt = mysqli_num_rows($isrecord);
                                    }
                                else
                                    {
                                        $cnt = 0;
                                    }
                                if ($cnt > 0)
                                    {
                                    } ?>                                
                                <!-- Modal HTML -->                               
                            <div id="service-front-view" class="modal fade">                                    
                            <div class="modal-dialog modal-sm modal-md ">                                       
                                <div class="modal-content">                                            <div class="modal-header">                                                
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>                                                
                                    <h4 class="modal-title"><?php echo $label_language_values['service_front_view']; ?></h4>                                            
                                </div>                                           
                    <div class="modal-body">                                                
                        <div class="ct-custom-radio">                                                    
                            <div class="alert alert-success mymessage_assign_design_service">                                                        
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>                                           <strong><?php echo $label_language_values['success']; ?></strong>                                                    
                            </div>                                               
                            <ul class="ct-radio-list">                                                        
                                                                              
                            <li class="fln w100">                                                            
                                <input <?php if ($t == 1) { echo "checked";
                                } ?> type="radio" id="front-service-box-view" class="cta-radio design_radio_btn"   name="front-service-view-radio" value="1" />                                                  <label for="front-service-box-view"><span></span> <?php echo $label_language_values['front_service_box_view']; ?></label>                                                 <img src="<?php echo SITE_URL; ?>assets/images/services/default_service.png" style="height: 100px;width: 300px;">                                                    
                            </li>                                                        
                            <li class="fln w100">                                                            
                                <input <?php if ($t == 2){ echo "checked";
                                } ?> type="radio" id=" front-service-dropdown-view" class="cta-radio design_radio_btn" name="front-service-view-radio" value="2" />                                                  
                                <label for="front-service-dropdown-view"><span></span><?php echo $label_language_values[' front_service_dropdown_view'];?></label>                                            <img src="<?php echo SITE_URL; ?>assets/images/services/default_service.png" style="height: 50px;width: 400px;">                                                       
                            </li>                                              
                        </ul>                                                
                    </div>                                            
                </div>                                            
                    <div class="modal-footer cb">
                     <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $label_language_values['close']; ?>
                     </button>                                                         
                </div>
            </div>                                    
        </div>                               
    </div>                            
    </td>                            
    <td>                                
        <button id="ct-add-new-service" class="btn btn-success" value="add new service"><i class="fa fa-plus"></i><?php echo $label_language_values['add_cleaning_service']; ?></button>    
    </td>                        
    </tr>                        
    </tbody>                    
    </table>                
    </div>            
    </div>            
    <div id="hr"></div>            
    <div class="tab-pane active" id="">

<!-- services list -->                
<div class="tab-content ct-clean-services-right-details">  
    <div class="tab-pane active col-lg-12 col-md-12 col-sm-12 col-xs-12">  
        <div id="accordion" class="panel-group">  
         <ul class="nav nav-tab nav-stacked my-sortable-services" id="sortable-services"  >                                 
            <?php $i = 0;
            $getservice = $objservice->getalldata();
            if($getservice->num_rows > 0){
            while ($arr = @mysqli_fetch_array($getservice))
            {
                $i++; ?>                                    
                <li class="panel panel-default ct-clean-services-panel mysortlist" data-id="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" id="servicelist<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" data-position="<?php if(isset($arr['position'])){ echo $arr['position']; }else{ } ?>" >
                 <div class="panel-heading">
                     <h4 class="panel-title">
                        <div class="cta-col7">
                            <div class="pull-left">  
                                <i class="fa fa-th-list"></i><span class="badge" id="color_back<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" style="background-color:<?php if(isset($arr['color'])){ echo $arr['color']; }else{ } ?>;" title="<?php echo $label_language_values['service_color_badge']; ?>"></span>        
                            </div> 
                          <span class="ct-clean-service-title-name" id="title_ser<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>"><?php echo ucfirst($arr['title']); ?></span></div>
                          <div class="pull-right cta-col5">
                          <div class="cta-col6 cta-manage-price-addons">     
                           <a data-id="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" data-name="<?php if(isset($arr['title'])){ echo $arr['title']; }else{ } ?>" class=" manage-price-calculation-btn pull-left btn-circle btn-primary btn-sm mybtnforassignmethods" title="<?php echo $label_language_values['manage_price_calculation_methods']; ?>"> <i class="fa fa-calculator" title="<?php echo $label_language_values['manage_price_calculation_methods']; ?>"></i><?php echo $label_language_values['pricing']; ?></a>                                               
                           <a data-id="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" data-name="<?php if(isset($arr['title'])){ echo $arr['title']; }else{ } ?>" class="manage-addons-btn pull-left btn-circle btn-info btn-sm mybtnforassignaddons" title="Manage addons of this service"> <i class="fa fa-puzzle-piece" title="<?php echo $label_language_values['manage_addons_of_this_service']; ?>"></i><?php echo $label_language_values['add_ons']; ?></a>
                         </div>                                                    
                    <div class="cta-col4 cta-enabe-disable">
                        <label for="sevice-endis-<?php echo $i; ?>">
                            <input data-id="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" class='myservice_status' data-toggle="toggle" data-size="small" type='checkbox' <?php if ($arr['status'] == 'E') { echo "checked"; }
                        else { echo ""; } ?> id="sevice-endis-<?php echo $i; ?>" data-on="<?php echo $label_language_values['enable']; ?>" data-off="<?php echo $label_language_values['disable']; ?>" data-onstyle='success' data-offstyle='danger'/>	 </label>
                    </div>
                 <div class="pull-right">                                                        
                    <div class="cta-col1">                                                
                    <?php $t = $objservice->service_isin_use($arr['id']);
                    if (isset($t) && $t > 0)
                    { ?>  
                    <a data-toggle="popover" class="delete-clean-service-btn pull-right btn-circle btn-danger btn-sm" rel="popover" data-placement='top' title="<?php echo $label_language_values['service_is_booked']; ?>"> <i class="fa fa-ban"></i></a> <?php } 
                    else { ?>
                     <a id="ct-delete-service<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" data-toggle="popover" class="delete-clean-service-btn pull-right btn-circle btn-danger btn-sm" rel="popover" data-placement='left' title="<?php echo $label_language_values['delete_this_service']; ?>?"> <i class="fa fa-trash" title="<?php echo $label_language_values['delete_service']; ?>"></i></a>                                                               
        <div id="popover-delete-servicess" style="display: none;">     
            <div class="arrow"></div>  
            <table class="form-horizontal" cellspacing="0">
              <tbody> 
                 <tr>   
                    <td>  
                        <a data-serviceid="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" data-imagename="<?php if(isset($arr['image'])){ echo $arr['image']; }else{ } ?>" value="Delete" class="btn btn-danger btn-sm service-delete-button" ><?php echo $label_language_values['yes']; ?></a>           
                         <button id="ct-close-popover-delete-service" class="btn btn-default btn-sm" href="javascript:void(0)" data-serviceid="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>"><?php echo $label_language_values['cancel']; ?></button>
                     </td> 
                  </tr>
             </tbody>
        </table>                                                                
        </div>                                                            <?php  } ?>                                                            
        <!-- end pop up -->                                                        
    </div>                                                        
<div class="ct-show-hide pull-right">
     <input type="checkbox" name="ct-show-hide" class="ct-show-hide-checkbox" id="myid<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" >
     <!--Added Serivce Id-->
      <label class="ct-show-hide-label" for="myid<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>"></label>                                                        
</div> 
</div> 
</div>                                            
</h4>                                        
</div>			
<div id="detail_myid<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" class="service_detail panel-collapse collapse">					
    <div class="panel-body">							
        <div class="ct-service-collapse-div col-sm-7 col-md-6 col-lg-5 col-xs-12">									
            <form id="editform_service<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" method="post" type="" class="slide-toggle" >								<table class="ct-create-service-table">
                <tbody>	
                	<tr>
                    	<td><label for="ct-service-color-tag"><?php echo $label_language_values['color_tag']; ?></label></td>
                    	<td><input type="text" name="txtedtcolor" id="ct-service-color-tag<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" class="form-control demo edtservicecolor<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" data-control="saturation" value="<?php if(isset($arr['color'])){ echo $arr['color']; }else{ } ?>">
                        </td>
                    </tr>
                     <tr>											
                        <td><label for="ct-service-title"><?php echo $label_language_values['service_title']; ?></label></td>
                        <td><input type="text" name="txtedtservicetitle" class="form-control edtservicetitle<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" id="ct-service-title<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" value="<?php if(isset($arr['title'])){ echo $arr['title']; }else{ } ?>" /></td>	
                    </tr>												
                    <tr>
                        <td><label for="ct-service-desc"><?php echo $label_language_values['service_description']; ?></label></td>
                    	<td><textarea id="ct-service-desc" class="form-control edtservicedesc<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" ><?php echo $arr['description']; ?></textarea></td>	
                    </tr>
                    <tr>
                        <td><label for="ct-service-desc"><?php echo $label_language_values['service_image']; ?></label></td>
                    	<td>
                        <div class="ct-clean-service-image-uploader">	<?php if ($arr['image'] == '')
                        {
                            $imagepath = SITE_URL . "assets/images/default_service.png";
                        }
                        else
                        {
                            $imagepath = SITE_URL . "assets/images/services/" . $arr['image'];
                        } ?>						
                        <img data-imagename="" id="pcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>serviceimage" src="<?php echo $imagepath; ?>" class="ct-clean-service-image br-100" height="100" width="100">						
                        <?php if ($arr['image'] == '') { ?>				
                        <label for="ct-upload-imagepcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" class="ct-clean-service-img-icon-label old_cam_ser<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>">	
                        <i class="ct-camera-icon-common br-100 fa fa-camera" id="pcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>camera"></i>	
                    	<i class="pull-left fa fa-plus-circle fa-2x" id="ctsc<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>plus"></i>		
                        </label>						
                    <?php } ?>						
                    <input data-us="pcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" class="hide ct-upload-images" type="file" name="" id="ct-upload-imagepcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" data-id="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" />						
                    <label for="ct-upload-imagepcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" class="ct-clean-service-img-icon-label new_cam_ser ser_cam_btn<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>">					<i class="ct-camera-icon-common br-100 fa fa-camera" id="pcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>camera"></i>			
                    <i class="pull-left fa fa-plus-circle fa-2x" id="ctsc<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>plus"></i>					</label>						
                    <?php if ($arr['image'] !== '') { ?>				
                    <a id="ct-remove-service-imagepcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" data-pclsid="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" data-service_id="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" class="pull-left br-100 btn-danger bt-remove-service-img btn-xs ser_del_icon ser_new_del<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" rel="popover" data-placement='left' title="<?php echo $label_language_values['remove_image']; ?>"> <i class="fa fa-trash" title="<?php echo $label_language_values['remove_service_image']; ?>"></i></a>						
                     <?php  } ?>						
                     <a id="ct-remove-service-imagepcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" data-pclsid="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" data-service_id="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" class="pull-left br-100 btn-danger bt-remove-service-img btn-xs new_del_ser del_btn_popup<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" rel="popover" data-placement='left' title="<?php echo $label_language_values['remove_image']; ?>"> <i class="fa fa-trash" title="<?php echo $label_language_values['remove_service_image']; ?>"></i></a>						
                     <div id="popover-ct-remove-service-imagepcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" style="display: none;">								
                    <div class="arrow"></div>							
                    <table class="form-horizontal" cellspacing="0">		
                        <tbody>										
                            <tr>
                                <td>
                            	<a href="javascript:void(0)" id="" value="Delete" data-service_id="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" class="btn btn-danger btn-sm delete_image" type="submit"><?php echo $label_language_values['yes']; ?></a>											
                                <a href="javascript:void(0)" id="ct-close-popover-service-image" class="btn btn-default btn-sm" data-service_id="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>"><?php echo $label_language_values['cancel']; ?></a>
                                </td>								
                            </tr>										
                        </tbody>								
                    </table>					
                </div><!-- end pop up -->			
            </div>				
            <label class="error_image" ></label>				
            <div id="ct-image-upload-popuppcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" class="ct-image-upload-popup modal fade" tabindex="-1" role="dialog">						
            <div class="vertical-alignment-helper">							<div class="modal-dialog modal-md vertical-align-center">		<div class="modal-content">										<div class="modal-header">	
                    <div class="col-md-12 col-xs-12">
                    	<a data-us="pcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" class="btn btn-success ct_upload_img1" data-imageinputid="ct-upload-imagepcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" data-id="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>"><?php echo $label_language_values['crop_and_save']; ?></a>
                    	<button type="button" class="btn btn-default hidemodal" data-dismiss="modal" aria-hidden="true"><?php echo $label_language_values['cancel']; ?>
                        </button>
                    </div>												
                </div>											
                <div class="modal-body">
                    <img id="ct-preview-imgpcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" style="width: 100%;"  />
            </div>	
            <div class="modal-footer">
                <div class="col-md-12 np"> 
                    <div class="col-md-12 np"> 
                     <div class="col-md-4 col-xs-12">  
                       <label class="pull-left"><?php echo $label_language_values['file_size']; ?></label> 
                       <input type="text" class="form-control" id="pclsfilesize<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" name="filesize"/> 
                    </div> 
                  <div class="col-md-4 col-xs-12">            
                   <label class="pull-left">H</label> <input type="text" class="form-control" id="pcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>h" name="h" />                                           
                </div>      
               <div class="col-md-4 col-xs-12">  
                <label class="pull-left">W</label> <input type="text" class="form-control" id="pcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>w" name="w" />                                                          
               </div>
                <!-- hidden crop params -->                                   
             <input type="hidden" id="pcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>x1" name="x1" />
             <input type="hidden" id="pcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>y1" name="y1" /> 
             <input type="hidden" id="pcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>x2" name="x2" /> 
             <input type="hidden" id="pcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>y2" name="y2" /> 
             <input type="hidden" id="pcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>id" name="id" value="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" />
             <input id="pclsctimage<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" type="hidden" name="ctimage" />  
            <input type="hidden" id="recordid" value="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>"> 
            <input type="hidden" id="pcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>ctimagename" class="pclsimg" name="ctimagename" value="<?php if(isset($arr['image'])){ echo $arr['image']; }else{ } ?>" /> 
            <input type="hidden" id="pcls<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>newname" value="service_" /> 
       </div>
       </div> 
    </div>
</div>
</div>
</div>	
</div>	
</td>
</tr>
<tr>
    <td></td>
    <td><a id="" name="" data-id="<?php if(isset($arr['id'])){ echo $arr['id']; }else{ } ?>" class="btn btn-success ct-btn-width  edtservicebtn" ><?php echo $label_language_values['update']; ?></a><button type="reset" class="btn btn-default ct-btn-width ml-30"><?php echo $label_language_values['reset']; ?></button>
    </td>
    </tr>
    </tbody>
</table>
</div>
</form>		
</div>	
</div>
</li><?php } } ?>                            
</ul>                            
<ul  class="new-service-scroll">                               
 <li>                                    
    <!-- add new clean service pop up -->                                    
    <div class="panel panel-default ct-clean-services-panel ct-add-new-service">                                        
        <div class="panel-heading">
            <h4 class="panel-title">
                <div class="cta-col8">
                 <div class="pull-left">
                   <i class="fa fa-th-list"></i><span class="badge" style="background-color:#555;" title="Service color badge"></span>
                </div> 
                 <span class="ct-service-title-name"></span>
         </div>                                               
         <div class="pull-right cta-col4">
            <div class="pull-right">
                <div class="ct-show-hide pull-right">
                    <input type="checkbox" name="ct-show-hide" checked="checked" class="ct-show-hide-checkbox" id="sp3" >
                    <!--Added Serivce Id--> 
                    <label class="ct-show-hide-label" for="sp3"></label> 
                </div>                                                    
            </div>   
        </div>
    </h4> 
</div>                                        
<div id="" class="panel-collapse collapse in detail_sp3">
    <div class="panel-body">
         <div class="ct-service-collapse-div col-sm-7 col-md-6 col-lg-5 col-xs-12">
          <form id="addservice_form" method="post" type="" class="slide-toggle" >
            <table class="ct-create-service-table">
              <tbody>
               <tr> 
                <td><label for="ct-service-color-tag"><?php echo $label_language_values['color_tag']; ?></label></td>
                <td><input type="text" name="txtcolor" id="ct-service-color-tag" class="form-control demo mycolortag"  data-control="saturation" value="#0088cc"></td> 
              </tr>
              <tr> 
                <td><label for="ct-service-title"><?php echo $label_language_values['service_title']; ?></label></td>     
                <td><input type="text" name="txtservicetitle" class="form-control myservicetitle" id="ct-service-title" /></td> </tr>                                                        
                <tr> 
                  <td><label for="ct-service-desc"><?php echo $label_language_values['service_description']; ?></label></td> 
                  <td><textarea id="ct-service-desc" class="form-control myservicedesc"></textarea></td> 
                 </tr>
                 <tr> 
                   <td><label for="ct-service-desc"><?php echo $label_language_values['service_image']; ?></label>
                   </td> 
                 <td>
                    <i class="myserviceimage"></i>
                    <div class="ct-clean-service-image-uploader">
                    <img id="pcasserviceimage" src="<?php echo SITE_URL; ?>assets/images/default_service.png" class="ct-clean-service-image br-100" height="100" width="100">
                  <!--<label for="ct-clean-service-image" class="ct-clean-service-img-icon-label">-->              
                    <label for="ct-upload-imagepcas" class="ct-clean-service-img-icon-label"> 
                    <i class="ct-camera-icon-common br-100 fa fa-camera"></i> 
                    <i class="pull-left fa fa-plus-circle fa-2x"></i>
                    </label> 
                    <input data-us="pcas" class="hide ct-upload-images" type="file" name="" id="ct-upload-imagepcas" />             
                    <a id="ct-remove-service-imagepcas" id="ct_service_image" class="pull-left br-100 btn-danger bt-remove-service-img btn-xs hide" rel="popover" data-placement='left' title="<?php echo $label_language_values['remove_image']; ?>"> <i class="fa fa-trash" title="<?php echo $label_language_values['remove_service_image']; ?>"></i></a> 
                  <label><b class="error-service" style="color:red;"></b></label> 
                  <div id="popover-ct-remove-service-imagepcas" style="display: none;"> 
                    <div class="arrow"></div>
                     <table class="form-horizontal" cellspacing="0">       
                        <tbody> 
                            <tr>
                             <td>
                                <a href="javascript:void(0)" id="" value="Delete" class="btn btn-danger btn-sm" type="submit"><?php echo $label_language_values['yes']; ?></a>
                                 <a href="javascript:void(0)" id="ct-close-popover-service-imagepcas" class="btn btn-default btn-sm" href="javascript:void(0)"><?php echo $label_language_values['cancel'];?></a>                                       
                             </td>
                         </tr> 
                     </tbody>
                 </table>
             </div><!-- end pop up -->
         </div>
		 <label class="error_image" ></label>
     <div id="ct-image-upload-popuppcas" class="ct-image-upload-popup modal fade" tabindex="-1" role="dialog">
        <div class="vertical-alignment-helper"> 
          <div class="modal-dialog modal-md vertical-align-center">            
            <div class="modal-content"> 
             <div class="modal-header"> 
                <div class="col-md-12 col-xs-12">                 
                 <a data-us="pcas" class="btn btn-success ct_upload_img1" data-imageinputid="ct-upload-imagepcas"><?php echo $label_language_values['crop_and_save']; ?></a>             
                 <button type="button" class="btn btn-default hidemodal" data-dismiss="modal" aria-hidden="true"><?php echo $label_language_values['cancel']; ?></button>                
             </div> 
         </div> 
         <div class="modal-body">
          <img id="ct-preview-imgpcas" class="ct-preview-img" style="width: 100%;" />
      </div> 
      <div class="modal-footer">
        <div class="col-md-12 np">
         <div class="col-md-12 np">
            <div class="col-md-4 col-xs-12"> 
                <label class="pull-left"><?php echo $label_language_values['file_size']; ?></label> <input type="text" class="form-control" id="pcasfilesize" name="filesize" />
            </div> 
             <div class="col-md-4 col-xs-12">
                <label class="pull-left">H</label> <input type="text" class="form-control" id="pcash" name="h" />  
            </div> 
            <div class="col-md-4 col-xs-12"> 
               <label class="pull-left">W</label> <input type="text" class="form-control" id="pcasw" name="w" /> 
           </div> 
         <!-- hidden crop params --> 
         <input type="hidden" id="pcasx1" name="x1" />
         <input type="hidden" id="pcasy1" name="y1" /> 
         <input type="hidden" id="pcasx2" name="x2" />  
         <input type="hidden" id="pcasy2" name="y2" /> 
         <input type="hidden" id="pcasid" name="id" value="" /> 
         <label class="error_image" ></label>                                                                
         <input id="pcasctimage" type="hidden" name="ctimage" />
         <input type="hidden" id="lastrecordid" value="service_"> 
         <input type="hidden" id="pcasctimagename" class="pcasimg" name="ctimagename" value="" />
          <input type="hidden" id="pcasnewname" value="service_" />
      </div>          
     </div> 
 </div>
</div> 
</div>                                                                        
</div>                                                                    </div>                                                                
</td>                                                           
 </tr>															
 <tr>																
    <td></td>																<td>
        <a  id="" name="" class="btn btn-success ct-btn-width  myserviceaddbtn" ><?php echo $label_language_values['save']; ?></a>																	
        <button id="reset_service_form" type="reset" class="btn btn-default ct-btn-width ml-30"><?php echo $label_language_values['reset']; ?></button>
    </td>				
</tr> 
</tbody>
</table> 
</div> 
</form>
 </div> 
</div> 
</div>                              
 </li>
</ul>
</div> 
</div>
</div>
 </div> 
</div>        
<!-- file upload preview -->    
</div>
</div>
<?php include (dirname(__FILE__) . '/footer.php'); ?>
<script type="text/javascript">    
    var ajax_url = '<?php echo AJAX_URL; ?>';    
    var ajaxObj = {'ajax_url':'<?php echo AJAX_URL; ?>'};    
    var servObj={'site_url':'<?php echo SITE_URL . 'assets/images/business/'; ?>'};    
    var imgObj={'img_url':'<?php echo SITE_URL . 'assets/images/'; ?>'};
</script>
