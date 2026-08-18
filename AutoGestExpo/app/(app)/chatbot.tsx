import { Button, Empty, Loading, Muted, ScrollScreen } from '../../src/components/ui';
import { api } from '../../src/lib/api';
import { colors } from '../../src/lib/theme';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useState } from 'react';
import { StyleSheet, Text, TextInput, View } from 'react-native';

type ChatMessage = { id?: number; sender: string; message: string };

export default function ChatbotScreen() {
  const queryClient = useQueryClient();
  const [text, setText] = useState('');

  const query = useQuery({
    queryKey: ['chatbot'],
    queryFn: async () => (await api.get('/chatbot')).data as { messages: ChatMessage[] },
  });

  const send = useMutation({
    mutationFn: async (message: string) => (await api.post('/chatbot/messages', { message })).data as { reply: string },
    onSuccess: () => {
      setText('');
      queryClient.invalidateQueries({ queryKey: ['chatbot'] });
    },
  });

  if (query.isLoading) {
    return <Loading />;
  }

  if (query.isError) {
    return (
      <ScrollScreen>
        <Empty>El chatbot móvil aún no está desplegado en la API de producción. Vehículos y órdenes sí funcionan.</Empty>
      </ScrollScreen>
    );
  }

  const messages = query.data?.messages ?? [];

  return (
    <ScrollScreen>
      <Muted>Pregunta por tu vehículo, citas u horarios.</Muted>
      {messages.length === 0 ? <Empty>Escribe un mensaje para empezar.</Empty> : null}
      {messages.map((item: ChatMessage, index: number) => {
        const mine = item.sender === 'user';
        return (
          <View key={item.id ?? index} style={[styles.bubble, mine ? styles.mine : styles.bot]}>
            <Text style={mine ? styles.mineLabel : styles.botLabel}>{mine ? 'Tú' : 'AutoGest Bot'}</Text>
            <Text style={mine ? styles.mineText : styles.botText}>{item.message}</Text>
          </View>
        );
      })}
      <View style={styles.composer}>
        <TextInput
          value={text}
          onChangeText={setText}
          placeholder="Escribe tu pregunta"
          placeholderTextColor={colors.muted}
          style={styles.input}
        />
        <Button title={send.isPending ? 'Enviando…' : 'Enviar'} disabled={!text.trim() || send.isPending} onPress={() => send.mutate(text.trim())} />
      </View>
    </ScrollScreen>
  );
}

const styles = StyleSheet.create({
  bubble: { borderRadius: 16, padding: 12, gap: 4, maxWidth: '92%' },
  mine: { alignSelf: 'flex-end', backgroundColor: colors.primary },
  bot: { alignSelf: 'flex-start', backgroundColor: colors.card, borderWidth: 1, borderColor: colors.border },
  mineLabel: { color: '#e0f2fe', fontWeight: '800', fontSize: 12 },
  botLabel: { color: colors.primary, fontWeight: '800', fontSize: 12 },
  mineText: { color: '#fff', fontSize: 15, lineHeight: 21 },
  botText: { color: colors.text, fontSize: 15, lineHeight: 21 },
  composer: { gap: 8, marginTop: 8 },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 12,
    padding: 14,
    backgroundColor: colors.card,
    color: colors.text,
    fontSize: 16,
  },
});
