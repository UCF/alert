<?php get_header(); the_post();?>
<div class="row page-content">
	<div class="span7 offset1">
		<h2 class="page-header">Emergency Communication Tests Thursday <small>November 15, 2012</small></h2>
		<p class="muted">This information was last updated on <strong>November 15, 2012 at 2:12 P.M. (EDT)</strong></p>
		<div class="alert-content">
			<p>UCF’s preparations for potential emergency situations include regular tests of communication tools that provide critical information to students, staff and faculty members, family members and the public.</p>
			<p>Several of those UCF Alert communication tools will be tested on Thursday, Sept. 20.</p>
			<p><strong>Outdoor sirens</strong>, which emit a tone followed by voice instructions, will be tested at noon. This test will feature two new sirens, at the Student Union and Garage H.</p>
			<p>A <strong>test emergency text message and e-mail</strong> will be sent at about 1 p.m. Messages should be received by all students and staff and faculty members who have updated cell phone information on file through the MyUCF Web site and who have not opted out of receiving emergency messages.</p>
			<p>Updates can be made through the following steps after signing on to the MyUCF site.</p>
			<p><strong>Students:</strong> Click on Student Self Service, then Personal Information and then Phone Numbers.</p>
			<p><strong>Faculty and Staff:</strong> Click on Employee Self Service, then Personal Information and then Phone Numbers.</p>
			<p>The <strong>university’s main Web site</strong>, <a href="http://www.ucf.edu/">www.ucf.edu</a>, will be replaced beginning at 4 p.m. with a text-only version that will be the primary source of information during an emergency. To accommodate large numbers of visitors, the emergency website does not include links and search functions that are typically found on the UCF home page.</p>
			<p>Users are encouraged to bookmark other university Web pages that they may need in advance of the test, which will last no more than one hour.</p>
			<p>UCF provides many campus personnel who assist with emergency responses with HEARO <strong>emergency radios</strong>. Radios, which also are in key campus buildings such as residence halls and the Student Union, will be tested at 2 p.m.</p>
			<p>Thirty-five UCF buildings are equipped with<strong> indoor notification systems</strong> that emit tones followed by voice instructions during emergencies. Those systems will be tested briefly between 3 and 3:30 p.m. The tests in each building will last no more than a couple of minutes.</p>
			<p><strong>WUCF-89.9 FM</strong> will conduct a test of its emergency broadcast capabilities at 4 p.m.</p>
			<p>UCF’s <strong>Twitter and Facebook pages</strong>, which serve as additional methods of notification, will be updated with information about the tests.</p>
			<p>Visit the <a href="http://www.emergency.ucf.edu/ucfalert.html">UCF Alert Web site</a> for more information on the communication system that helps to keep the campus safe and informed during emergencies.</p>
		</div>
	</div>
	<div class="span3" id="sidebar">
		<div class="about">
			<h3>About this Page</h3>
			<p>
				This page is the official source of alert information for the University of Central Florida. In the event of an emergency, check this page for updated information. This page is updated and maintained by the Office of News and Information.
			</p>
		</div>
		<hr />
		<div class="contact">
			<?php 
				$contacts = get_posts(array(
					'post_type'    => 'contact_information',
					'numberposts'  => -1));
				foreach($contacts as $contact) {
					$name  = get_post_meta($contact->ID, 'contact_information_name', True);
					$value = get_post_meta($contact->ID, 'contact_information_value', True);
					echo sprintf('<h3>%s</h3><p>%s</p>', $name, $value);
				} 
			?>
		</div>
		<hr />
		<div class="previous">
			<h3>Previous Alerts</h3>
			<p>There are no previous alerts</p>
		</div>
	</div>
</div>
<?php get_footer(); ?>