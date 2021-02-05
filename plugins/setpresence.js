// https://github.com/Eegras/dragory_modmailbot_addons/blob/master/plugins/setpresence.js

// Usage:
//   !setpresence <state>
//   <state> can be one of:
//      invisible
//      dnd
//      away
//      online

module.exports = function({ bot, knex, config, commands }) {
  commands.addInboxThreadCommand('setpresence', '[presence$]', (msg, args, thread) => {
    bot.editStatus(args.presence);
  });
}
