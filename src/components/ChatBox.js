import React, { useState } from 'react';

function ChatBox() {
  const [messages, setMessages] = useState([]);
  const [input, setInput] = useState('');
  const [firstMessageSent, setFirstMessageSent] = useState(false); // Estado para verificar si ya se ha enviado el primer mensaje

  const sendMessage = async () => {
    if (input.trim()) {
      // Primero, agregamos el mensaje del usuario solo al principio
      const userMessage = { sender: 'user', text: input };
      setMessages((prevMessages) => [...prevMessages, userMessage]);

      // Establecemos que ya se ha enviado el primer mensaje (inmediatamente al hacer clic en "Enviar")
      setFirstMessageSent(true);

      // Verificar el mensaje antes de enviarlo
      console.log("Enviando mensaje:", input);
  
      try {
        const response = await fetch('http://localhost/handlechat.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ message: input }),
          mode: 'cors', // Asegurarse de que el modo CORS está habilitado
        });
  
        if (!response.ok) {
          throw new Error('Error en la solicitud al servidor');
        }
  
        const data = await response.json();
        console.log("Respuesta de la API:", data);

        // Verificar si hay una respuesta válida del bot
        if (data.response) {
          const botMessage = { sender: 'bot', text: data.response };

          // Ahora solo agregamos la respuesta del bot
          setMessages((prevMessages) => [...prevMessages, botMessage]);
        } else {
          console.error("No se recibió una respuesta válida");
        }
      } catch (error) {
        console.error('Error del servidor:', error);
      }

      setInput(''); // Limpiar el campo de entrada
    }
  };

  // Función para detectar la tecla Enter
  const handleKeyDown = (e) => {
    if (e.key === 'Enter') {
      sendMessage();
    }
  };

  return (
    <div>
      <div className="chat-window">
        {/* Mostramos el mensaje de bienvenida si aún no se ha enviado el primer mensaje */}
        {!firstMessageSent && (
          <div className="bot placeholder">
            Pregúntame lo que quieras sobre enfermedades infecciosas.
          </div>
        )}

        {/* Mostrar los mensajes del chat */}
        {messages.map((msg, index) => (
          <div key={index} className={msg.sender}>
            {msg.text}
          </div>
        ))}
      </div>

      <input
        type="text"
        value={input}
        onChange={(e) => setInput(e.target.value)}
        onKeyDown={handleKeyDown} // Agregado para detectar Enter
        placeholder="Escribe tu consulta..."
      />
      <div className="button-div">
        <button onClick={sendMessage}>Enviar</button>
      </div>
    </div>
  );
}

export default ChatBox;
