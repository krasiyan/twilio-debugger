Debugger-а работи в два режима – web и cli. И в двата изкарва всички логове преди последно записания лог (по SID), който играе ролята на буфер. Ако такъв лог няма изкарва max 50 лога (configurable). 

В уеб режима има 2 optional GET параметъра – loglevel и lastsid:
loglevel  - приема възможните стойности за типа на log-а (1-warning, 0-error, 3-account). Debugger-а показва всички логове до буферния с този тип.
lastsid – override-ва буферния лог (временно). Debugger-а показва всички логове до буферния, а ако той не съществува отново стига до максимално упоменатата бройка
В уеб режима страницата се рефрешва на всеки 10 секунди, като SID-а на буферния лог не се презаписва!

В CLI режима debugger-а изкарва всички логове до буферния и ги праща на посочения mail като html съобщение. След всяко изпращане се логва във logfile-а и SID-а на буферния лог се презаписва. При липса на нови логове не се изпраща email.
----------
The debugger works in both web and cli modes. In both modes it outputs all logs newer than the log SID in `last_sid.txt`. If the last SID is not saved, 50 logs entries are outputed.
When accessing the debugger from the web it accepts two optional GET paramters - loglevel and lastsid.
The webpage auto-refreshes every 10 seconds. The last known SID in the file does not get overwriten.
When using the debugger from the CLI it fetches all logs up to the last known SID and sends them over to the configured mail.
This overwrites the last known SID in `last_sid.txt`
