# epanel
<p align="center"><img src="https://develop.netmechanics.net/uploads/epanel.png" width="400"></p>
### epanel starter pack

PHP CodeIgniter HMVC generic purpose prebuilt control panel
support php 5.6 to 7.3
<h5>starter pack comes with</h5>
<ul>
    <li>users account</li>
    <li>dynamic roles</li>
    <li>authentication and authorization</li>
    <li>notifications domains</li>
    <li>real time notifications</li>
    <li>single area support</li>
    <li>custom permissions and functionality</li>
    <li>multi language support</li>
    <li>actions log</li>
    <li>multi theme color support</li>
    <li>user profiling</li>
    <li>supporting multi modules</li>
    <li>example posts module</li>
</ul>

<h5>installation guide</h5>

<ul>
    <li>change base_url in "application/config/config.php"</li>
    <li>change epanel-link in "application/config/epanel_config.php"</li>
    <li>change password and ip salt in "application/config/epanel_config.php"</li>
    <li>change codemechanics user data in "application/migrations/002_seed_epanel.php"</li>
    <li>change users in "application/migrations/003_users_domain.php" Or remove file if not needed</li>
    <li>review Or remove (if not needed) migration files number 3 and 4 at "application/migrations/" if migrations removed be sure to update migration_version in "application/config/migration.php" </li>
    <li>configer database parameters in "application/config/database.php"</li>
    

</ul>

<br/>
<h5>credit to</h5>
<ul>
    <li><a href="https://codeigniter.com/" target="_blank">CodeIgniter 3.1.11</a> the brain of the project</li>
    <li><a href="https://bitbucket.org/wiredesignz/codeigniter-modular-extensions-hmvc" target="_blank">wiredesignz</a> to go HMVC way </li>
</ul>

 <span>if you think we forget to mention other work please contact me at Mahmoud@Alghool.net