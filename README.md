SELECT p.projectid, p.projectname
FROM projects p
JOIN project_user_information pui ON p.projectid = pui.project_id
WHERE pui.user_id = [emp_id]



CREATE TABLE IF NOT EXISTS timesheets (
    timesheet_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    week_start DATE NOT NULL,
    week_end DATE NOT NULL,
    CONSTRAINT fk_user
        FOREIGN KEY(user_id) 
        REFERENCES user_details(user_id)
);

CREATE TABLE IF NOT EXISTS timesheet_entries (
    entry_id SERIAL PRIMARY KEY,
    timesheet_id INTEGER NOT NULL,
    projectid INTEGER NOT NULL,
    day_of_the_week TEXT,
    work_date DATE,
    hours_worked TIME WITHOUT TIME ZONE,
    notes TEXT,
    project_role TEXT,
    CONSTRAINT fk_timesheet
        FOREIGN KEY(timesheet_id) 
        REFERENCES timesheets(timesheet_id),
    CONSTRAINT fk_project
        FOREIGN KEY(projectid) 
        REFERENCES projects(projectid)
);
