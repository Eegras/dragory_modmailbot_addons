// Stolen directly from reply.js, just added some code to close the thread.
// If reply.js changes, this must too, probably.

module.exports = ({ bot, knex, config, commands }) => {
  commands.addInboxThreadCommand('replyclose', '[text$]', async (msg, args, thread) => {
    if (! args.text && msg.attachments.length === 0) {
      utils.postError(msg.channel, 'Text or attachment required');
      return;
    }

    const replied = await thread.replyToUser(msg.member, args.text || '', msg.attachments, false);
    if (replied) {
      msg.delete();
      thread.close();  // This is what was changed from reply.js
    }
  }, {
    aliases: ['rc']
  });


  // Anonymous replies only show the role, not the username
  commands.addInboxThreadCommand('anonreplyclose', '[text$]', async (msg, args, thread) => {
    if (! args.text && msg.attachments.length === 0) {
      utils.postError(msg.channel, 'Text or attachment required');
      return;
    }

    const replied = await thread.replyToUser(msg.member, args.text || '', msg.attachments, true);
    if (replied) {
      msg.delete();
      thread.close();  // This is what was changed from reply.js
    }
  }, {
    aliases: ['arc']
  });
};
