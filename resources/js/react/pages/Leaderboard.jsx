import { useMemo, useRef } from "react";
import { leaderboard } from "../data/leaderboard";
import LeaderboardPodium from "../components/leaderboard/LeaderboardPodium";
import LeaderboardRow from "../components/leaderboard/LeaderboardRow";
import CurrentUserStickyCard from "../components/leaderboard/CurrentUserStickyCard";
import useStickyVisibility from "../components/leaderboard/useStickyVisibility";
import { getPodiumUsers, getRemainingUsers, getCurrentUser, getUserAbove,
    getXpToPassAbove,
} from "../components/leaderboard/leaderboardUtils";

export default function Leaderboard() {
    const podiumUsers = useMemo(() => getPodiumUsers(leaderboard), []);
    const remainingUsers = useMemo(() => getRemainingUsers(leaderboard), []);
    const currentUser = useMemo(() => getCurrentUser(leaderboard), []);
    const currentUserInPodium = !!currentUser && currentUser.rank <= 3;

    const userAbove = useMemo(
        () => (currentUser ? getUserAbove(leaderboard, currentUser) : null),
        [currentUser]
    );

    const xpToPassAbove = currentUser ? getXpToPassAbove(currentUser, userAbove) : null;
    const podiumRef = useRef(null);
    const currentRowRef = useRef(null);

    // Watch whichever surface actually holds the current user — the podium
    // itself if they're top 3, otherwise their specific row in the list.
    const stickyTargetRef = currentUserInPodium ? podiumRef : currentRowRef;
    
    const { isTargetVisible, isPulsing } = useStickyVisibility(stickyTargetRef, {
        enabled: !!currentUser,
    });

    const showSticky = !!currentUser && !isTargetVisible;

    return (
        <main className="akd-content">
            <section className="akd-leaderboard">
                <LeaderboardPodium ref={podiumRef} users={podiumUsers}
                    currentUserId={currentUser?.id}
                    isPulsing={currentUserInPodium && isPulsing}
                    xpToPassAbove={xpToPassAbove} rankAbove={userAbove?.rank ?? null}
                />

                <ul className="akd-leaderboard__list">
                    {remainingUsers.map((user) => {
                        const isCurrent = user.id === currentUser?.id;

                        return (
                            <LeaderboardRow key={user.id}
                                ref={isCurrent ? currentRowRef : null} user={user}
                                isCurrentUser={isCurrent}
                                isPulsing={isCurrent && isPulsing}
                                xpToPassAbove={isCurrent ? xpToPassAbove : null}
                                rankAbove={isCurrent ? (userAbove?.rank ?? null) : null}
                            />
                        );
                    })}
                </ul>
            </section>

            <CurrentUserStickyCard user={currentUser} visible={showSticky}
                xpToPassAbove={xpToPassAbove} rankAbove={userAbove?.rank ?? null}
            />
        </main>
    );
}