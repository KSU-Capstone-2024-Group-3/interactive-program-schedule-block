# interactive-program-schedule-block

Created using https://www.npmjs.com/package/@wordpress/create-block. 

Developed on a local wordpress docker container. The docker build file is included. Install the docker engine here: https://docs.docker.com/engine/install/

KSU Capstone Group 3 Spring 2024.

<pre>
User Manual (UM)
•	How to start the development enironment
1.  Move the "docker-compose.yaml" to a directory you are okay with a WordPress installation being made in.
2.  Download and install the Docker Engine. 
3.  In the directory you moved "docker-compose.yaml" run "docker-compose up -d"
4.  Done! 
WordPress will be hosted on localhost:8000 with username: "root" and password: "toor" 
phpMyAdmin will be hosted on localhost:8080  with username: "root" and password: "password" 
  
•	How to install the plugin
1.	Go to your WordPress plugin menu.
2.	Click the “Add New Plugin” button. 
3.	Click the “Upload Plugin” button.
4.	Click the “Choose File" button. 
5.	Select the plugin folder, if needed compress the folder into a .zip file.
6.	Click the “Install Now” button. 
7.	Activate the plugin!

•	How to add a show.
1.  Navigate to the "Add Show" sub menu in the Schulder admin menu.
2.  Fill out the form.
3.  Click "Add Show"
  
•	How to edit a show.
1.  Navigate to the "Edit Show" sub menu in the Schulder admin menu.
2.  Choose a show to edit from the drop down.
3.  Change the form.
4.  Click "Save Show".
  
•	How to archive a show.
There are several ways to do this.
1. In the "Scheduler" main admin menu in the "Airing" table all the way on the right there are buttons to archive each show.
2. In the "Edit Show" sub menu, there is an archive button on the bottom of the page for archiving the show currently being edited.
  
•	How to unarchive a show.
1. In the "Archive" admin submenu in the "Archive" table all the way on the right there are buttons to unarchive each show.
  
•	How to add the calendar block to a page
**Note: the display in the editor will be different than what's rendered on the page!
1. In the admin page go to "pages", hover over the page you want to edit and click "Edit".
2. Then use the top left blue plus to add a block.
3. Navigate to "Interactive Program Scheduler Block"
4. Add it to the page.
5. Save by clicking "update".
  
•	Admin logs.
Keeps logs of all actions done in the backend. (add, edit, archive, unarchive)
  
</pre>
