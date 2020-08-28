// This assumes it is sitting in a ./plugins/ directory.
// Adjust this require to fit if the script is somewhere else
const utils = require('../src/utils');

// Usage:
//   !setnick <new nickname>
// Must be run inside an inbox thread.  It will set the requester's nickname the
//mainGuild and tells the user anonymously.  
// It does not handle errors well at all.

module.exports = function({ bot, knex, config, commands }) {
  commands.addInboxThreadCommand('setnick', '[nickname$]', (msg, args, thread) => {
    try {
      var changenick = bot.editGuildMember(config.mainGuildId, thread.user_id, { nick: args['nickname'] }, "Requested in modmail");
      changenick.then((result) => {
        thread.replyToUser(msg.member, "Your nickname has been set to " + args['nickname'], [], true);
      });
    } catch (err) {
      thread.postSystemMessage("Generic error applying setnick: " + err);
    }
  });
}
