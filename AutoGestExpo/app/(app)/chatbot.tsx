import { Empty, Loading } from '../../src/components/ui';
import { api, apiErrorMessage } from '../../src/lib/api';
import { useAuth } from '../../src/lib/auth';
import { colors } from '../../src/lib/theme';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useState } from 'react';
import {
  FlatList,
  KeyboardAvoidingView,
  Platform,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';

type ChatMessage = { id?: number; sender: string; message: string };

const suggestions = ['Quiero una cita', 'Estado de mi orden', 'Horarios del taller'];

function extractVehicleChips(messages: ChatMessage[]): { plate: string; label: string }[] {
  const lastBot = [...messages].reverse().find((item) => item.sender !== 'user');
  if (!lastBot?.message) {
    return [];
  }

  const chips: { plate: string; label: string }[] = [];
  const pattern = /(?:^|\n)\s*[•\-]\s*([A-Z0-9]{2,4}[-\s]?\d{2,4})\s*[—\-]\s*([^\n]+)/gi;
  let match: RegExpExecArray | null;

  while ((match = pattern.exec(lastBot.message)) !== null) {
    const plate = match[1].replace(/\s+/g, '-').toUpperCase();
    const name = match[2].trim();
    chips.push({ plate, label: `${name} (${plate})` });
  }

  return chips;
}

export default function ChatbotScreen() {
  const { user } = useAuth();
  const queryClient = useQueryClient();
  const [text, setText] = useState('');
  const [sendError, setSendError] = useState('');

  const query = useQuery({
    queryKey: ['chatbot', user?.id],
    queryFn: async () => (await api.get('/chatbot')).data as { messages: ChatMessage[] },
  });

  const send = useMutation({
    mutationFn: async (message: string) => (await api.post('/chatbot/messages', { message })).data as { reply: string },
    onSuccess: () => {
      setText('');
      setSendError('');
      queryClient.invalidateQueries({ queryKey: ['chatbot'] });
    },
    onError: (error: unknown) => {
      setSendError(apiErrorMessage(error, 'No se pudo enviar el mensaje. Intenta de nuevo.'));
    },
  });

  function submit(message?: string) {
    const value = (message ?? text).trim();
    if (!value || send.isPending) {
      return;
    }
    setSendError('');
    send.mutate(value);
  }

  if (query.isLoading) {
    return <Loading />;
  }

  if (query.isError) {
    return (
      <View style={styles.screen}>
        <Empty
          icon="chatbubbles-outline"
          title="Chat no disponible"
          actionLabel="Reintentar"
          onAction={() => void query.refetch()}
        >
          {apiErrorMessage(query.error, 'El asistente no responde ahora. Vehículos y órdenes sí funcionan.')}
        </Empty>
      </View>
    );
  }

  const messages = query.data?.messages ?? [];
  const vehicleChips = extractVehicleChips(messages);

  return (
    <KeyboardAvoidingView style={styles.screen} behavior={Platform.OS === 'ios' ? 'padding' : undefined} keyboardVerticalOffset={88}>
      <FlatList
        data={messages}
        keyExtractor={(item, index) => String(item.id ?? index)}
        contentContainerStyle={styles.thread}
        keyboardShouldPersistTaps="handled"
        ListEmptyComponent={
          <View style={styles.welcome}>
            <Text style={styles.welcomeTitle}>Hola, soy el asistente de AutoGest</Text>
            <Text style={styles.welcomeCopy}>Pregunta por tu vehículo, una cita o el horario del taller.</Text>
            <View style={styles.chips}>
              {suggestions.map((item) => (
                <Pressable key={item} onPress={() => submit(item)} style={styles.chip}>
                  <Text style={styles.chipText}>{item}</Text>
                </Pressable>
              ))}
            </View>
          </View>
        }
        renderItem={({ item }) => {
          const mine = item.sender === 'user';
          return (
            <View style={[styles.bubble, mine ? styles.mine : styles.bot]}>
              <Text style={mine ? styles.mineLabel : styles.botLabel}>{mine ? 'Tú' : 'AutoGest Bot'}</Text>
              <Text style={mine ? styles.mineText : styles.botText}>{item.message}</Text>
            </View>
          );
        }}
      />
      {vehicleChips.length ? (
        <View style={styles.chips}>
          {vehicleChips.map((item) => (
            <Pressable key={item.plate} onPress={() => submit(item.plate)} style={styles.chip}>
              <Text style={styles.chipText}>{item.label}</Text>
            </Pressable>
          ))}
        </View>
      ) : null}
      {sendError ? <Text style={styles.sendError}>{sendError}</Text> : null}
      <View style={styles.composer}>
        <TextInput
          value={text}
          onChangeText={setText}
          placeholder="Escribe tu pregunta"
          placeholderTextColor={colors.muted}
          style={styles.input}
          returnKeyType="send"
          onSubmitEditing={() => submit()}
        />
        <Pressable disabled={!text.trim() || send.isPending} onPress={() => submit()} style={[styles.send, (!text.trim() || send.isPending) && styles.sendOff]}>
          <Text style={styles.sendText}>{send.isPending ? '…' : 'Enviar'}</Text>
        </Pressable>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.page, padding: 16, gap: 12 },
  thread: { flexGrow: 1, gap: 10, paddingBottom: 12 },
  welcome: {
    backgroundColor: colors.card,
    borderRadius: 20,
    padding: 20,
    borderWidth: 1,
    borderColor: colors.border,
    gap: 8,
  },
  welcomeTitle: { fontWeight: '800', fontSize: 18, color: colors.text, letterSpacing: -0.3 },
  welcomeCopy: { color: colors.muted, lineHeight: 20 },
  chips: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginTop: 6 },
  chip: {
    backgroundColor: colors.primarySoft,
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 8,
    minHeight: 40,
    justifyContent: 'center',
  },
  chipText: { color: colors.primary, fontWeight: '700', fontSize: 13 },
  bubble: { borderRadius: 18, padding: 12, gap: 4, maxWidth: '88%' },
  mine: { alignSelf: 'flex-end', backgroundColor: colors.primary, borderBottomRightRadius: 6 },
  bot: { alignSelf: 'flex-start', backgroundColor: colors.card, borderWidth: 1, borderColor: colors.border, borderBottomLeftRadius: 6 },
  mineLabel: { color: '#e0f2fe', fontWeight: '800', fontSize: 12 },
  botLabel: { color: colors.primary, fontWeight: '800', fontSize: 12 },
  mineText: { color: '#fff', fontSize: 15, lineHeight: 21 },
  botText: { color: colors.text, fontSize: 15, lineHeight: 21 },
  composer: {
    flexDirection: 'row',
    gap: 8,
    alignItems: 'center',
    backgroundColor: colors.card,
    borderRadius: 22,
    borderWidth: 1,
    borderColor: colors.border,
    padding: 6,
    paddingLeft: 12,
  },
  input: {
    flex: 1,
    paddingHorizontal: 10,
    paddingVertical: 10,
    color: colors.text,
    fontSize: 16,
    minHeight: 44,
  },
  send: {
    backgroundColor: colors.primary,
    borderRadius: 16,
    paddingHorizontal: 16,
    minHeight: 44,
    justifyContent: 'center',
  },
  sendOff: { opacity: 0.45 },
  sendText: { color: '#fff', fontWeight: '800' },
  sendError: { color: colors.danger, fontWeight: '600', fontSize: 13, paddingHorizontal: 4 },
});
