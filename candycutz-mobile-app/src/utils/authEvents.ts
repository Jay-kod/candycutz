type AuthEventListener = () => void;

class AuthEventEmitter {
  private listeners: { [event: string]: AuthEventListener[] } = {};

  on(event: string, callback: AuthEventListener) {
    if (!this.listeners[event]) {
      this.listeners[event] = [];
    }
    this.listeners[event].push(callback);
    return () => this.off(event, callback);
  }

  off(event: string, callback: AuthEventListener) {
    if (!this.listeners[event]) return;
    this.listeners[event] = this.listeners[event].filter((cb) => cb !== callback);
  }

  emit(event: string) {
    if (!this.listeners[event]) return;
    this.listeners[event].forEach((cb) => {
      try {
        cb();
      } catch (e) {}
    });
  }
}

export const authEvents = new AuthEventEmitter();
