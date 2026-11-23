# Cap late work at the pass mark (gradepenalty_cap)

Many universities do not take a percentage off late coursework. They cap it: work handed in up to five working days late keeps its mark only up to the pass mark (for example 40%), and work later than that gets zero. Moodle's built-in late penalty deducts percentages. This grade penalty plugin applies the cap rule instead.

- Late work is capped at the grade item's "Grade to pass". If the item has none, at a site percentage (default 40%).
- Work already below the pass mark keeps its mark.
- The late window is 5 working days by default (Monday to Friday, site time zone). Weekends can be counted.
- After the window: the minimum grade (default), or keep the cap.
- Teacher overrides, locked grades and extensions work as with core penalties: the due date Moodle passes in already includes extensions and overrides.

Set up:

1. Site administration > Grades > Grade penalties > Manage penalty plugins: enable "Cap late work at the pass mark". Use it instead of "Late submission penalties", not with it.
2. Site administration > Grades > Grade penalties > Manage supported activities: tick Assignment.
3. Set the pass mark, window and what happens after it on the plugin's settings page.
4. In each assignment, turn on "Grade penalties" under Grade.

Moodle 5.0 to 5.3 (grade penalty plugins arrived in 5.0). Built by DevLMS: https://devlms.com
