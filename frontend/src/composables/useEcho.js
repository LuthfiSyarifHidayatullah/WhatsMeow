import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

let echoInstance = null

export function useEcho() {
  if (!echoInstance) {
    echoInstance = new Echo({
      broadcaster: 'pusher',
      key: import.meta.env.VITE_PUSHER_APP_KEY || 'mpp-local-key',
      cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
      wsHost: import.meta.env.VITE_PUSHER_HOST || '127.0.0.1',
      wsPort: import.meta.env.VITE_PUSHER_PORT || 6001,
      forceTLS: false,
      disableStats: true,
      enabledTransports: ['ws', 'wss'],
    })
  }

  function listenMonitoring(callback) {
    echoInstance.channel('monitoring')
      .listen('.ChatEscalatedEvent', callback)
      .listen('.NewMessageEvent', callback)
  }

  function listenChatSession(sessionId, callback) {
    echoInstance.private(`chat-session.${sessionId}`)
      .listen('.NewMessageEvent', callback)
  }

  function listenOfficerChannel(officerId, callback) {
    echoInstance.private(`officer.${officerId}`)
      .listen('.ChatEscalatedEvent', callback)
  }

  function leaveChannel(channel) {
    echoInstance.leave(channel)
  }

  return {
    echo: echoInstance,
    listenMonitoring,
    listenChatSession,
    listenOfficerChannel,
    leaveChannel,
  }
}
